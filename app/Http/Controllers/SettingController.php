<?php

namespace App\Http\Controllers;

use App\Mail\ReportMail;
use App\Models\Setting;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Console\Application as Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan as ArtisanFacade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Support\Currencies;

class SettingController extends Controller
{
    private array $fields;

    private const REPORT_TYPES = ['sale', 'profit', 'product_profit', 'expense', 'purchase', 'transaction'];
    private const REPORT_PERIODS = ['today', 'week', 'month', 'last_month', 'year', 'custom'];
    private const BACKUP_FREQUENCIES = ['daily', 'weekly', 'monthly', 'yearly'];

    public function __construct()
    {
        $this->fields = [
            "invoice_prefix" => ["label" => "Invoice prefix", "type" => "text", "rules" => "required|string|max:20", "group" => "business"],
            "business_name" => ["label" => "Business name", "type" => "text", "rules" => "nullable|string|max:255", "group" => "business"],
            "business_address" => ["label" => "Business address", "type" => "text", "rules" => "nullable|string|max:255", "group" => "business"],
            "business_phone" => ["label" => "Business phone", "type" => "text", "rules" => "nullable|string|max:50", "group" => "business"],
            "business_email" => ["label" => "Business email", "type" => "email", "rules" => "nullable|email|max:255", "group" => "business"],
            "currency" => ["label" => "Currency", "type" => "select", "options" => Currencies::LIST, "rules" => "nullable|string|max:10", "group" => "business"],
            "timezone" => [
                "label" => "Time zone",
                "type" => "select",
                "options" => $this->timezoneOptions(),
                "grouped" => true,
                "rules" => "nullable|string|max:64|timezone",
                "group" => "business",
                "default" => config('app.timezone', 'UTC'),
            ],
        ];
    }

    /**
     * All valid PHP timezone identifiers, grouped by region (Africa, America, Asia, ...)
     * for rendering as <optgroup>s in the settings select.
     */
    private function timezoneOptions(): array
    {
        $groups = [];

        foreach (\DateTimeZone::listIdentifiers() as $tz) {
            $parts = explode('/', $tz, 2);
            $region = count($parts) === 2 ? $parts[0] : 'Other';
            $groups[$region][$tz] = $tz;
        }

        ksort($groups);

        return $groups;
    }

    public function edit()
    {
        $values = collect($this->fields)->mapWithKeys(function ($field, $key) {
            if (!empty($field['sensitive'])) {
                return [$key => ''];
            }

            return [$key => Setting::get($key, $field['default'] ?? '')];
        });

        $logoPath = Setting::get('business_logo_path', null);
        $logoUrl = $logoPath ? route('media', ['path' => $logoPath]) : null;

        $reportEmails = json_decode(Setting::get('report_emails', '[]'), true) ?: [''];
        $reportTypes = json_decode(Setting::get('report_types', '[]'), true) ?: [];
        $reportFrequency = Setting::get('report_frequency', 'monthly');
        $reportEnabled = (bool) Setting::get('report_enabled', false);

        $backupPath = Setting::get('backup_path', '');
        $backupFrequency = Setting::get('backup_frequency', 'daily');
        $backupEnabled = (bool) Setting::get('backup_enabled', false);

        return view('settings.edit', [
            'fields' => $this->fields,
            'values' => $values,
            'logoUrl' => $logoUrl,
            'reportEmails' => $reportEmails,
            'reportTypes' => $reportTypes,
            'reportFrequency' => $reportFrequency,
            'reportEnabled' => $reportEnabled,
            'backupPath' => $backupPath,
            'backupFrequency' => $backupFrequency,
            'backupEnabled' => $backupEnabled,
        ]);
    }

    private function validateBackupPath(?string $path, $validator): void
    {
        if (! $path) {
            return;
        }

        $normalized = strtolower(trim($path));

        if (preg_match('/^c:[\\\\\/]?/', $normalized)) {
            $validator->errors()->add('backup_path', 'Backup path cannot be on the C: drive.');
        }
    }

    public function update(Request $request)
    {
        $rules = collect($this->fields)->mapWithKeys(fn ($field, $key) => [$key => $field['rules']])->all();
        $rules['business_logo'] = 'nullable|image|max:2048';
        $rules['remove_logo'] = 'sometimes|boolean';
        $rules['report_emails'] = 'nullable|array';
        $rules['report_emails.*'] = 'nullable|email';
        $rules['report_types'] = 'nullable|array';
        $rules['report_types.*'] = 'in:' . implode(',', self::REPORT_TYPES);
        $rules['report_frequency'] = 'nullable|in:weekly,monthly,yearly';
        $rules['report_enabled'] = 'sometimes|boolean';
        $rules['backup_path'] = 'nullable|string|max:500';
        $rules['backup_frequency'] = 'nullable|in:'.implode(',', self::BACKUP_FREQUENCIES);
        $rules['backup_enabled'] = 'sometimes|boolean';

        $validator = validator($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            $this->validateBackupPath($request->input('backup_path'), $validator);

            if ($request->boolean('backup_enabled') && ! $request->filled('backup_path')) {
                $validator->errors()->add('backup_path', 'A backup path is required to enable automatic backups.');
            }
        });

        $data = $validator->validate();

        foreach ($this->fields as $key => $field) {
            $value = $data[$key] ?? null;

            if (!empty($field['sensitive']) && ($value === '' || $value === null)) {
                continue;
            }

            Setting::set($key, $value);
        }

        if ($request->boolean('remove_logo')) {
            $old = Setting::get('business_logo_path', null);
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('business_logo_path', null);
        } elseif ($request->hasFile('business_logo')) {
            $old = Setting::get('business_logo_path', null);
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('business_logo')->store('branding', 'public');
            Setting::set('business_logo_path', $path);
        }

        $emails = array_values(array_filter($data['report_emails'] ?? [], fn ($e) => !empty($e)));
        Setting::set('report_emails', json_encode($emails));
        Setting::set('report_types', json_encode($data['report_types'] ?? []));
        if (! empty($data['report_frequency'])) {
            Setting::set('report_frequency', $data['report_frequency']);
        }
        Setting::set('report_enabled', $request->boolean('report_enabled') ? '1' : '0');

        Setting::set('backup_path', $data['backup_path'] ?? '');
        Setting::set('backup_frequency', $data['backup_frequency'] ?? 'daily');
        Setting::set('backup_enabled', $request->boolean('backup_enabled') ? '1' : '0');

        return back()->with('success', 'Settings updated.');
    }

    public function sendReportNow(Request $request)
    {
        $data = $request->validate([
            'report_emails' => 'required|array|min:1',
            'report_emails.*' => 'required|email',
            'report_types' => 'required|array|min:1',
            'report_types.*' => 'in:' . implode(',', self::REPORT_TYPES),
            'report_period' => 'nullable|in:' . implode(',', self::REPORT_PERIODS),
            'start_date' => 'required_if:report_period,custom|nullable|date',
            'end_date' => 'required_if:report_period,custom|nullable|date|after_or_equal:start_date',
        ]);

        [$from, $to] = $this->resolveReportPeriod(
            $data['report_period'] ?? 'month',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
        );

        foreach ($data['report_emails'] as $email) {
            Mail::to($email)->send(new ReportMail($data['report_types'], $from, $to));
        }

        return back()->with('success', 'Report for ' . $from->format('M j, Y') . ' – ' . $to->format('M j, Y')
            . ' sent to ' . count($data['report_emails']) . ' recipient(s).');
    }

    /** Turn a period key (or a custom start/end) into a [from, to] pair covering whole days. */
    private function resolveReportPeriod(string $period, ?string $start, ?string $end): array
    {
        $today = Carbon::today();

        return match ($period) {
            'today'      => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'week'       => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'last_month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'year'       => [$today->copy()->startOfYear(), $today->copy()->endOfYear()],
            'custom'     => [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()],
            default      => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
        };
    }

    /** Create a backup right now and hand it to the browser as a download. */
    public function backupNow(Request $request)
    {
        $sourcePath = config('database.connections.sqlite.database');

        if (! $sourcePath || ! File::exists($sourcePath)) {
            return back()->with('error', 'Database file not found.');
        }

        $filename = 'pixel-backup-manual-'.now()->format('Y-m-d_His').'.sqlite';
        $tempPath = storage_path('app/'.$filename);

        File::copy($sourcePath, $tempPath);

        // Also drop a copy in the configured backup folder, if one is set.
        $backupPath = Setting::get('backup_path', '');
        if ($backupPath) {
            $this->validateBackupPath($backupPath, $v = validator([], []));
            if (! $v->errors()->has('backup_path')) {
                if (! File::isDirectory($backupPath)) {
                    File::makeDirectory($backupPath, 0755, true);
                }
                File::copy($sourcePath, rtrim($backupPath, '\\/').DIRECTORY_SEPARATOR.$filename);
            }
        }

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /** Replace the live database with an uploaded .sqlite file — after safety-backing-up the current one. */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:512000', // 500MB ceiling
            'confirm_restore' => 'required|accepted',
        ]);

        $uploaded = $request->file('backup_file');

        if (strtolower($uploaded->getClientOriginalExtension()) !== 'sqlite') {
            return back()->with('error', 'The uploaded file must be a .sqlite file.');
        }

        $destinationPath = config('database.connections.sqlite.database');

        // Safety net: back up the current live database before overwriting it.
        ArtisanFacade::call('backup:run', ['--label' => 'pre-restore']);

        File::copy($uploaded->getRealPath(), $destinationPath);

        return back()->with('success', 'Database restored. A safety backup of the previous database was also saved.');
    }
}