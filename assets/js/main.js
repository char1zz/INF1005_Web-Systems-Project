// assets/js/main.js

document.addEventListener('DOMContentLoaded', function () {

    // ── Mega menu hover on nav links ──
    document.querySelectorAll('.sf-nav-link[data-mega]').forEach(link => {
        link.addEventListener('mouseenter', () => {
            document.querySelectorAll('.sf-mega').forEach(m => m.classList.remove('open'));
            const target = document.getElementById(link.dataset.mega);
            if (target) target.classList.add('open');
        });
    });
    document.querySelector('.sf-nav')?.addEventListener('mouseleave', () => {
        document.querySelectorAll('.sf-mega').forEach(m => m.classList.remove('open'));
    });

    // ── Mobile drawer ──
    const overlay = document.getElementById('sf-overlay');
    const drawer  = document.getElementById('sf-drawer');

    document.getElementById('sf-hamburger')?.addEventListener('click', () => {
        overlay?.classList.add('open');
    });
    document.getElementById('sf-drawer-close')?.addEventListener('click', () => {
        overlay?.classList.remove('open');
    });
    overlay?.addEventListener('click', (e) => {
        if (!drawer?.contains(e.target)) overlay.classList.remove('open');
    });

    // ── Drawer sub-menu toggles ──
    document.querySelectorAll('.sf-drawer-item[data-sub]').forEach(item => {
        item.addEventListener('click', () => {
            const sub   = document.getElementById(item.dataset.sub);
            const arrow = item.querySelector('.sf-drawer-arrow');
            sub?.classList.toggle('open');
            arrow?.classList.toggle('open');
        });
    });

    // ── data-confirm dialogs ──
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });

    // ── Auto-dismiss flash alerts after 5 s ──
    document.querySelectorAll('.sf-alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // ── Qty +/- controls ──
    document.querySelectorAll('.sf-qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const ctrl = btn.closest('.sf-qty-ctrl');
            const val  = ctrl?.querySelector('.sf-qty-val, input[name="quantity"]');
            if (!val) return;
            const isInput = val.tagName === 'INPUT';
            let current = parseInt(isInput ? val.value : val.textContent) || 1;
            const delta = btn.dataset.dir === '+' ? 1 : -1;
            current = Math.max(1, current + delta);
            if (isInput) { val.value = current; val.dispatchEvent(new Event('change')); }
            else val.textContent = current;
        });
    });



    // ── Homepage class modal ──
    const classModal = document.getElementById('sf-class-modal');
    const classDialog = classModal?.querySelector('.sf-class-modal-dialog');
    const classTitle = document.getElementById('sf-class-modal-title');
    const classDesc = document.getElementById('sf-class-modal-desc');
    const classBadge = document.getElementById('sf-class-modal-badge');
    const classDur = document.getElementById('sf-class-modal-dur');
    const classLevel = document.getElementById('sf-class-modal-level');
    const classRoom = document.getElementById('sf-class-modal-room');
    const classBenefits = document.getElementById('sf-class-modal-benefits');
    const classExpect = document.getElementById('sf-class-modal-expect');
    const classGear = document.getElementById('sf-class-modal-gear');
    const classShop = document.getElementById('sf-class-modal-shop');

    const renderList = (el, items = []) => {
        if (!el) return;
        el.innerHTML = '';
        items.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            el.appendChild(li);
        });
    };

    const openClassModal = (data) => {
        if (!classModal || !data) return;
        if (classTitle) classTitle.textContent = data.name || '';
        if (classDesc) classDesc.textContent = data.desc || '';
        if (classBadge) {
            classBadge.textContent = (data.type || '').toUpperCase();
            classBadge.className = 'sf-class-modal-badge badge-' + (data.type || 'spin');
        }
        if (classDur) classDur.textContent = data.dur || '';
        if (classLevel) classLevel.textContent = data.level || '';
        if (classRoom) classRoom.textContent = data.room || '';
        renderList(classBenefits, data.benefits || []);
        renderList(classExpect, data.expect || []);
        renderList(classGear, data.gear || []);
        renderList(classShop, data.shop || []);
        classModal.classList.add('open');
        classModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeClassModal = () => {
        if (!classModal) return;
        classModal.classList.remove('open');
        classModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.sf-class-modal-trigger').forEach(card => {
        card.addEventListener('click', () => {
            try {
                openClassModal(JSON.parse(card.dataset.classModal));
            } catch (e) {
                console.error('Unable to open class modal', e);
            }
        });
    });

    classModal?.querySelectorAll('[data-class-modal-close]').forEach(btn => {
        btn.addEventListener('click', closeClassModal);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeClassModal();
    });


});
