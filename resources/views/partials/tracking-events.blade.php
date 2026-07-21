{{-- Dispara eventos de conversão enfileirados pelo backend via
     session()->flash('tracking_events', [['name' => 'trial_start'], ...]).
     No-op sem fdTracking (tracking desligado) ou sem consentimento.
     Ver docs/marketing/instrumentacao-tracking.md --}}
@if(session('tracking_events'))
<script>
(function () {
    var events = @json(session('tracking_events'));
    function run() {
        if (!window.fdTracking || !Array.isArray(events)) return;
        events.forEach(function (ev) {
            if (ev && ev.name) window.fdTracking.track(ev.name, ev.params || {});
        });
    }
    if (document.readyState !== 'loading') run();
    else document.addEventListener('DOMContentLoaded', run);
})();
</script>
@endif
