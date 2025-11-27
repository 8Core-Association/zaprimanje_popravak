/**
 * Plaćena licenca
 * (c) 2025 Tomislav Galić <tomislav@8core.hr>
 * Suradnik: Marko Šimunović <marko@8core.hr>
 * Web: https://8core.hr
 * Kontakt: info@8core.hr | Tel: +385 099 851 0717
 * Sva prava pridržana. Ovaj softver je vlasnički i zabranjeno ga je
 * distribuirati ili mijenjati bez izričitog dopuštenja autora.
 */

class ObavijestiBell {
    constructor() {
        this.apiUrl = '/custom/seup/ajax/obavijesti_api.php';
        this.bellElement = null;
        this.dropdownElement = null;
        this.badgeElement = null;
        this.pollInterval = 60000;
        this.init();
    }

    init() {
        this.injectHTML();
        this.attachEventListeners();
        this.loadObavijesti();
        this.startPolling();
    }

    injectHTML() {
        const rightHeader = document.querySelector('#id-right .login_block_user');
        if (!rightHeader) {
            console.warn('SEUP: Cannot find header element, trying alternative selectors');
            const alternatives = [
                '#id-right',
                '.side-nav-vert',
                'header .tmenu',
                '.login_block'
            ];

            for (const selector of alternatives) {
                const element = document.querySelector(selector);
                if (element) {
                    this.injectIntoElement(element);
                    return;
                }
            }

            console.error('SEUP: Could not find suitable header element');
            return;
        }

        this.injectIntoElement(rightHeader);
    }

    injectIntoElement(targetElement) {
        const bellHTML = `
            <div class="seup-notification-bell-wrapper">
                <button class="seup-notification-bell" id="seupNotificationBell" title="Obavijesti">
                    <i class="fas fa-bell"></i>
                    <span class="seup-notification-badge" id="seupNotificationBadge" style="display: none;">0</span>
                </button>
                <div class="seup-notification-dropdown" id="seupNotificationDropdown" style="display: none;">
                    <div class="seup-notification-header">
                        <h4>Obavijesti</h4>
                    </div>
                    <div class="seup-notification-list" id="seupNotificationList">
                        <div class="seup-notification-loading">
                            <i class="fas fa-spinner fa-spin"></i> Učitavanje...
                        </div>
                    </div>
                </div>
            </div>
        `;

        targetElement.insertAdjacentHTML('beforeend', bellHTML);

        this.bellElement = document.getElementById('seupNotificationBell');
        this.dropdownElement = document.getElementById('seupNotificationDropdown');
        this.badgeElement = document.getElementById('seupNotificationBadge');
    }

    attachEventListeners() {
        if (!this.bellElement) return;

        this.bellElement.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.seup-notification-bell-wrapper')) {
                this.closeDropdown();
            }
        });
    }

    toggleDropdown() {
        const isVisible = this.dropdownElement.style.display !== 'none';
        if (isVisible) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.dropdownElement.style.display = 'block';
        this.loadObavijesti();
    }

    closeDropdown() {
        this.dropdownElement.style.display = 'none';
    }

    async loadObavijesti() {
        try {
            const response = await fetch(`${this.apiUrl}?action=dohvati`);
            const data = await response.json();

            if (data.success) {
                this.renderObavijesti(data.obavijesti);
                this.updateBadge(data.obavijesti);
            }
        } catch (error) {
            console.error('SEUP: Error loading notifications:', error);
            this.renderError();
        }
    }

    renderObavijesti(obavijesti) {
        const listElement = document.getElementById('seupNotificationList');
        if (!listElement) return;

        if (!obavijesti || obavijesti.length === 0) {
            listElement.innerHTML = '<div class="seup-notification-empty">Nemate novih obavijesti</div>';
            return;
        }

        const html = obavijesti.map(obavijest => this.renderObavijest(obavijest)).join('');
        listElement.innerHTML = html;

        listElement.querySelectorAll('.seup-notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const uuid = e.currentTarget.dataset.uuid;
                const link = e.currentTarget.dataset.link;
                this.markAsRead(uuid);
                if (link) {
                    window.location.href = link;
                }
            });

            const dismissBtn = item.querySelector('.seup-notification-dismiss');
            if (dismissBtn) {
                dismissBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const uuid = e.currentTarget.closest('.seup-notification-item').dataset.uuid;
                    this.dismissObavijest(uuid);
                });
            }
        });
    }

    renderObavijest(obavijest) {
        const tipClass = this.getTipClass(obavijest.tip);
        const prioritetClass = this.getPrioritetClass(obavijest.prioritet);
        const readClass = obavijest.procitano ? 'read' : 'unread';

        return `
            <div class="seup-notification-item ${tipClass} ${prioritetClass} ${readClass}"
                 data-uuid="${obavijest.id}"
                 data-link="${obavijest.link || ''}">
                <div class="seup-notification-content">
                    <div class="seup-notification-title">${this.escapeHtml(obavijest.naslov)}</div>
                    <div class="seup-notification-message">${this.escapeHtml(obavijest.poruka)}</div>
                    <div class="seup-notification-time">${this.formatTime(obavijest.datum)}</div>
                </div>
                <button class="seup-notification-dismiss" title="Odbaci">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }

    renderError() {
        const listElement = document.getElementById('seupNotificationList');
        if (!listElement) return;

        listElement.innerHTML = `
            <div class="seup-notification-error">
                <i class="fas fa-exclamation-triangle"></i>
                Greška pri učitavanju obavijesti
            </div>
        `;
    }

    updateBadge(obavijesti) {
        if (!this.badgeElement) return;

        const unreadCount = obavijesti.filter(o => !o.procitano).length;

        if (unreadCount > 0) {
            this.badgeElement.textContent = unreadCount > 99 ? '99+' : unreadCount;
            this.badgeElement.style.display = 'flex';
        } else {
            this.badgeElement.style.display = 'none';
        }
    }

    async markAsRead(uuid) {
        try {
            await fetch(`${this.apiUrl}?action=oznaci_procitano`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `uuid=${encodeURIComponent(uuid)}`
            });
            this.loadObavijesti();
        } catch (error) {
            console.error('SEUP: Error marking as read:', error);
        }
    }

    async dismissObavijest(uuid) {
        try {
            await fetch(`${this.apiUrl}?action=odbaci`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `uuid=${encodeURIComponent(uuid)}`
            });
            this.loadObavijesti();
        } catch (error) {
            console.error('SEUP: Error dismissing notification:', error);
        }
    }

    getTipClass(tip) {
        const tipMap = {
            'zaprimanje': 'tip-zaprimanje',
            'rok': 'tip-rok',
            'akt': 'tip-akt',
            'predmet': 'tip-predmet',
            'otprema': 'tip-otprema'
        };
        return tipMap[tip] || 'tip-default';
    }

    getPrioritetClass(prioritet) {
        const prioritetMap = {
            'hitan': 'prioritet-hitan',
            'normalan': 'prioritet-normalan',
            'info': 'prioritet-info'
        };
        return prioritetMap[prioritet] || 'prioritet-normalan';
    }

    formatTime(datetime) {
        if (!datetime) return '';

        const now = new Date();
        const created = new Date(datetime);
        const diffMs = now - created;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 60) {
            return `prije ${diffMins} min`;
        } else if (diffHours < 24) {
            return `prije ${diffHours}h`;
        } else if (diffDays === 1) {
            return 'jučer';
        } else if (diffDays < 7) {
            return `prije ${diffDays} dana`;
        } else {
            return created.toLocaleDateString('hr-HR');
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    startPolling() {
        setInterval(() => {
            this.loadObavijesti();
        }, this.pollInterval);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ObavijestiBell();
    });
} else {
    new ObavijestiBell();
}
