<x-error-page
    code="500"
    title="Something went wrong"
    message="An unexpected error happened on our side.<br>Please try again. If it keeps happening, contact an administrator."
    tone="danger"
    icon="alert"
    :retry-url="url()->current()"
    retry-label="Try again" />