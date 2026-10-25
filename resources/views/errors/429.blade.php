<x-error-page
    code="429"
    title="Too many requests"
    message="You're doing that too fast.<br>Wait a moment, then try again."
    tone="warn"
    icon="hourglass"
    :retry-url="url()->current()"
    retry-label="Try again" />