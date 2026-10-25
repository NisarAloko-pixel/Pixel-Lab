<x-error-page
    code="503"
    title="Back soon"
    message="The app is being updated or is in maintenance mode.<br>Please try again in a few minutes."
    tone="warn"
    icon="wrench"
    :retry-url="url()->current()"
    retry-label="Reload" />