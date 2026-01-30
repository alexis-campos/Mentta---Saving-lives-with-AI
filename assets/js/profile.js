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
     * Logout
     */
    logout() {
        window.location.href = 'logout.php';
    }
};

// Export for global access
window.Profile = Profile;
