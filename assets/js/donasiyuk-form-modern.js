document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.querySelector('.dyk-modern-form');
    if (!formContainer) return;

    formContainer.querySelectorAll('.dyk-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('dyk_nominal').value = this.getAttribute('data-amount');
        });
    });

    formContainer.querySelectorAll('.dyk-next-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const nextStep = this.getAttribute('data-next');
            formContainer.querySelectorAll('.dyk-step').forEach(step => step.style.display = 'none');
            const target = formContainer.querySelector('.dyk-step-' + nextStep);
            if (target) target.style.display = 'block';
        });
    });
});
