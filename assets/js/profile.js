/**
 * MENTTA - Profile Manager
 * Handles profile updates, contacts, and preferences
 */

const Profile = {
    /**
     * Update profile information
     */
    async updateProfile(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('api/patient/update-profile.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast('Perfil actualizado');
            } else {
                Utils.toast(data.error || 'Error al actualizar perfil');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            Utils.toast('Error de conexión');
        }
    },

    /**
     * Change password
     */
    async changePassword(event) {
        event.preventDefault();

        const form = event.target;
        const newPassword = form.querySelector('#new_password').value;
        const confirmPassword = form.querySelector('#confirm_password').value;

        if (newPassword !== confirmPassword) {
            Utils.toast('Las contraseñas no coinciden');
            return;
        }

        const formData = new FormData(form);

        try {
            const response = await fetch('api/patient/change-password.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast('Contraseña actualizada');
                form.reset();
            } else {
                Utils.toast(data.error || 'Error al cambiar contraseña');
            }
        } catch (error) {
            console.error('Error changing password:', error);
            Utils.toast('Error de conexión');
        }
    },

    /**
     * Show add contact modal
     */
    showAddContactModal() {
        document.getElementById('add-contact-modal').classList.add('active');
    },

    /**
     * Close add contact modal
     */
    closeAddContactModal() {
        document.getElementById('add-contact-modal').classList.remove('active');
        document.getElementById('add-contact-form').reset();
    },

    /**
     * Add emergency contact
     */
    async addContact(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('api/patient/add-emergency-contact.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast('Contacto agregado');
                this.closeAddContactModal();
                // Reload page to show new contact
                window.location.reload();
            } else {
                Utils.toast(data.error || 'Error al agregar contacto');
            }
        } catch (error) {
            console.error('Error adding contact:', error);
            Utils.toast('Error de conexión');
        }
    },

    /**
     * Delete emergency contact
     */
    async deleteContact(contactId) {
        if (!confirm('¿Eliminar este contacto de emergencia?')) {
            return;
        }

        const formData = new FormData();
        formData.append('contact_id', contactId);

        try {
            const response = await fetch('api/patient/delete-emergency-contact.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast('Contacto eliminado');
                // Remove from DOM
                const card = document.querySelector(`[data-contact-id="${contactId}"]`);
                if (card) card.remove();

                // Check if no contacts left
                const list = document.getElementById('contacts-list');
                if (list && list.children.length === 0) {
                    list.innerHTML = `
                        <p style="color: var(--text-tertiary); font-size: 0.875rem; text-align: center; padding: 1rem;">
                            No tienes contactos de emergencia configurados
                        </p>
                    `;
                }
            } else {
                Utils.toast(data.error || 'Error al eliminar contacto');
            }
        } catch (error) {
            console.error('Error deleting contact:', error);
            Utils.toast('Error de conexión');
        }
    },

    /**
     * Toggle analysis pause
     */
    async toggleAnalysisPause() {
        const toggle = document.getElementById('analysis-toggle');
        const pause = toggle.checked;

        if (pause) {
            const confirmed = confirm(
                '¿Seguro que deseas pausar el análisis emocional?\n\n' +
                'Las alertas de seguridad se desactivarán por 24 horas. ' +
                'Esto significa que no recibirás ayuda automática si compartes algo preocupante.'
            );

            if (!confirmed) {
                toggle.checked = false;
                return;
            }
        }

        const formData = new FormData();
        formData.append('pause', pause ? 'true' : 'false');

        try {
            const response = await fetch('api/patient/toggle-analysis-pause.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast(data.data.message);
                // Reload to show updated status
                if (pause) {
                    window.location.reload();
                }
            } else {
                Utils.toast(data.error || 'Error al cambiar configuración');
                toggle.checked = !pause;
            }
        } catch (error) {
            console.error('Error toggling analysis:', error);
            Utils.toast('Error de conexión');
            toggle.checked = !pause;
        }
    },

    /**
     * Confirm delete history
     */
    confirmDeleteHistory() {
        document.getElementById('confirm-delete-modal').classList.add('active');
    },

    /**
     * Close confirm delete modal
     */
    closeConfirmDeleteModal() {
        document.getElementById('confirm-delete-modal').classList.remove('active');
    },

    /**
     * Delete all chat history
     */
    async deleteHistory() {
        const formData = new FormData();
        formData.append('confirm', 'DELETE');

        try {
            const response = await fetch('api/patient/delete-chat-history.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast('Historial eliminado');
                this.closeConfirmDeleteModal();

                // Clear local storage session
                localStorage.removeItem('mentta-session-id');
            } else {
                Utils.toast(data.error || 'Error al eliminar historial');
            }
        } catch (error) {
            console.error('Error deleting history:', error);
            Utils.toast('Error de conexión');
        }
    },

    /**
     * Scanner State
     */
    scanner: null,

    /**
     * Open QR Scanner
     */
    async openScanner() {
        const modal = document.getElementById('qr-scanner-modal');
        const statusEl = document.getElementById('scanner-status');

        modal.classList.remove('hidden');
        // Force reflow
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');

        if (!this.scanner) {
            // Use Html5Qrcode (Pro API) for custom UI
            this.scanner = new Html5Qrcode("qr-reader");
        }

        statusEl.textContent = 'Iniciando cámara...';
        statusEl.className = 'text-white/80 text-sm font-medium animate-pulse';

        try {
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            // Prefer back camera
            await this.scanner.start(
                { facingMode: "environment" },
                config,
                (decodedText) => this.handleScanSuccess(decodedText),
                (errorMessage) => {
                    // Ignore frame parse errors
                }
            );

            statusEl.textContent = 'Cámara activa';
            statusEl.className = 'text-green-400 text-sm font-bold';

        } catch (err) {
            console.error("Error starting scanner", err);
            statusEl.textContent = 'Error: No se pudo acceder a la cámara';
            statusEl.className = 'text-red-400 text-sm font-bold';
            Utils.toast("Error al iniciar cámara: " + err);
        }
    },

    /**
     * Handle successful scan
     */
    handleScanSuccess(decodedText) {
        if (decodedText && decodedText.length === 6) {
            // Play success sound
            const audio = new Audio('assets/sounds/success.mp3'); // Optional
            // audio.play().catch(() => {});

            // Vibration feedback
            if (navigator.vibrate) navigator.vibrate(200);

            // Stop scanning logic but keep camera for a moment or close immediately

            // Update UI
            document.getElementById('scanner-status').textContent = '¡Código detectado!';
            document.getElementById('scanner-status').className = 'text-blue-400 text-lg font-bold bounce';

            this.closeScanner();

            // Fill input
            const input = document.querySelector('input[name="code"]');
            if (input) {
                input.value = decodedText;

                Utils.toast("Código detectado: " + decodedText);
                setTimeout(() => {
                    const form = input.closest('form');
                    if (form) form.requestSubmit();
                }, 500);
            }
        }
    },

    /**
     * Close Scanner
     */
    async closeScanner() {
        const modal = document.getElementById('qr-scanner-modal');

        if (this.scanner && this.scanner.isScanning) {
            try {
                await this.scanner.stop();
            } catch (e) {
                console.error("Error stopping scanner", e);
            }
        }

        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    },

    /**
     * Link with Psychologist
     */
    async linkPsychologist(event) {
        event.preventDefault();

        const form = event.target;
        const input = form.querySelector('input[name="code"]');
        const code = input.value.toUpperCase();
        const btn = form.querySelector('button');

        if (code.length !== 6) {
            Utils.toast('El código debe tener 6 caracteres');
            return;
        }

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Vinculando...';

        try {
            const response = await fetch('api/patient/link-psychologist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ code: code })
            });

            const data = await response.json();

            if (data.success) {
                Utils.toast(`¡Vinculado con ${data.psychologist_name}!`);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Utils.toast(data.error || 'Error al vincular');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (error) {
            console.error('Error linking:', error);
            Utils.toast('Error de conexión');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    },

    /**
     * Logout
     */
    logout() {
        window.location.href = 'logout.php';
    }
};

// Export for global access
window.Profile = Profile;
