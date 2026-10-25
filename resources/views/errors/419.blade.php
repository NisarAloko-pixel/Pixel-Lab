<x-error-page
    code="419"
    title="Session expired"
    message="Your page was open for too long, so the security token expired.<br>Open the page again and repeat what you were doing. Nothing was saved from the last attempt."
    tone="warn"
    icon="clock"
    :retry-url="url()->previous()"
    retry-label="Reload the page" />