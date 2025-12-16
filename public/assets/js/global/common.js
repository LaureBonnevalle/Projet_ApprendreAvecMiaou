document.addEventListener('DOMContentLoaded', function() {
    console.log('🔒 Common JS chargé');

    const togglePasswordIcons = document.querySelectorAll('.eyes');

    togglePasswordIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.closest('.password-container').querySelector('input[type="password"], input[type="text"]');

            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                }
            }
        });
    });
});