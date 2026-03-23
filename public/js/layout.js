// JavaScript do layout (menu mobile, sidebar BEM, etc.)
let _layoutInitialized = false;
let _mobileMenuHandler = null;
let _dropdownClickHandler = null;
let _dropdownHoverHandlers = [];
let _sidebarOpenHandler = null;
let _sidebarCloseHandler = null;
let _sidebarOverlayHandler = null;
let _sidebarLinkClickHandler = null;
let _sidebarGroupHandlers = [];
let _sidebarUserHandler = null;
let _sidebarToggleHandler = null;
let _mobileMenuLinkHandler = null;
const _dropdownOpenTimers = new WeakMap();
const _dropdownCloseTimers = new WeakMap();
const DROPDOWN_DELAY_MS = 100;

function applySidebarState(isCollapsed) {
    const sidebar = document.getElementById('sidebar');
    const layoutShell = document.getElementById('layout-shell');

    if (!sidebar) return;

    if (isCollapsed) {
        sidebar.classList.add('sidebar--collapsed');
        if (layoutShell) {
            layoutShell.classList.add('layout-sidebar-collapsed');
            layoutShell.classList.remove('layout-sidebar-expanded');
        }
        localStorage.setItem('sidebar-collapsed', 'true');
    } else {
        sidebar.classList.remove('sidebar--collapsed');
        if (layoutShell) {
            layoutShell.classList.remove('layout-sidebar-collapsed');
            layoutShell.classList.add('layout-sidebar-expanded');
        }
        localStorage.setItem('sidebar-collapsed', 'false');
    }
}

function initLayout() {
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        if (_mobileMenuHandler) {
            mobileMenuBtn.removeEventListener('click', _mobileMenuHandler);
            _mobileMenuHandler = null;
        }

        _mobileMenuHandler = function() {
            mobileMenu.classList.toggle('hidden');
            if (!mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.add('flex');
            } else {
                mobileMenu.classList.remove('flex');
            }
        };

        mobileMenuBtn.addEventListener('click', _mobileMenuHandler);
    }

    // Close mobile-menu on link click (landing page)
    if (mobileMenu) {
        if (_mobileMenuLinkHandler) {
            mobileMenu.removeEventListener('click', _mobileMenuLinkHandler);
            _mobileMenuLinkHandler = null;
        }
        _mobileMenuLinkHandler = function(e) {
            const link = e.target.closest('[data-link]');
            if (link) {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('flex');
            }
        };
        mobileMenu.addEventListener('click', _mobileMenuLinkHandler);
    }

    // Sidebar (authenticated area) - drawer on mobile + collapse/expand on desktop
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarOpenBtn = document.getElementById('sidebar-open-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');

    const isDesktop = () => window.matchMedia('(min-width: 768px)').matches;

    const openSidebarDrawer = () => {
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.add('sidebar--open');
        sidebarOverlay.classList.add('sidebar__overlay--visible');
        sidebarOverlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebarDrawer = () => {
        if (!sidebar || !sidebarOverlay) return;
        sidebar.classList.remove('sidebar--open');
        sidebarOverlay.classList.remove('sidebar__overlay--visible');
        sidebarOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    // Expose globally for SPA navigation
    window.closeSidebarDrawer = closeSidebarDrawer;

    // Open (mobile)
    if (sidebarOpenBtn && sidebar) {
        if (_sidebarOpenHandler) {
            sidebarOpenBtn.removeEventListener('click', _sidebarOpenHandler);
            _sidebarOpenHandler = null;
        }
        _sidebarOpenHandler = function () {
            openSidebarDrawer();
        };
        sidebarOpenBtn.addEventListener('click', _sidebarOpenHandler);
    }

    // Close (mobile)
    if (sidebarCloseBtn && sidebar) {
        if (_sidebarCloseHandler) {
            sidebarCloseBtn.removeEventListener('click', _sidebarCloseHandler);
            _sidebarCloseHandler = null;
        }
        _sidebarCloseHandler = function () {
            closeSidebarDrawer();
        };
        sidebarCloseBtn.addEventListener('click', _sidebarCloseHandler);
    }

    // Overlay click (mobile)
    if (sidebarOverlay) {
        if (_sidebarOverlayHandler) {
            sidebarOverlay.removeEventListener('click', _sidebarOverlayHandler);
            _sidebarOverlayHandler = null;
        }
        _sidebarOverlayHandler = function () {
            closeSidebarDrawer();
        };
        sidebarOverlay.addEventListener('click', _sidebarOverlayHandler);
    }

    // Close drawer on link click (mobile)
    if (sidebar) {
        if (_sidebarLinkClickHandler) {
            sidebar.removeEventListener('click', _sidebarLinkClickHandler);
            _sidebarLinkClickHandler = null;
        }
        _sidebarLinkClickHandler = function (e) {
            const link = e.target && e.target.closest ? e.target.closest('[data-link]') : null;
            if (!link) return;
            if (isDesktop()) return;
            closeSidebarDrawer();
        };
        sidebar.addEventListener('click', _sidebarLinkClickHandler);
    }

    // Dropdown menu - close on outside click
    if (_dropdownClickHandler) {
        document.removeEventListener('click', _dropdownClickHandler);
        _dropdownClickHandler = null;
    }

    _dropdownClickHandler = function(e) {
        const dropdownGroups = document.querySelectorAll('.relative.group');
        dropdownGroups.forEach(group => {
            const dropdownMenu = group.querySelector('.dropdown-menu');
            if (dropdownMenu && !group.contains(e.target)) {
                if (!dropdownMenu.classList.contains('opacity-0')) {
                    dropdownMenu.classList.add('opacity-0', 'invisible');
                    dropdownMenu.classList.remove('opacity-100', 'visible');
                }
            }
        });
    };

    document.addEventListener('click', _dropdownClickHandler);

    // Dropdown with hover delay (Solucoes menu)
    if (_dropdownHoverHandlers.length) {
        _dropdownHoverHandlers.forEach(({ element, enterHandler, leaveHandler }) => {
            element.removeEventListener('mouseenter', enterHandler);
            element.removeEventListener('mouseleave', leaveHandler);
        });
        _dropdownHoverHandlers = [];
    }

    const dropdownGroups = document.querySelectorAll('.nav-dropdown-buffer');

    const showPanel = (panel) => {
        panel.classList.remove('opacity-0', 'invisible', 'pointer-events-none', 'translate-y-2');
        panel.classList.add('opacity-100', 'visible', 'pointer-events-auto', 'translate-y-0');
    };

    const hidePanel = (panel) => {
        panel.classList.add('opacity-0', 'invisible', 'pointer-events-none', 'translate-y-2');
        panel.classList.remove('opacity-100', 'visible', 'pointer-events-auto', 'translate-y-0');
    };

    dropdownGroups.forEach(group => {
        const panel = group.querySelector('.nav-dropdown-panel');
        if (!panel) return;

        panel.classList.remove('group-hover:translate-y-0', 'group-hover:visible', 'group-hover:opacity-100', 'group-hover:pointer-events-auto');

        const enterHandler = () => {
            const closeTimer = _dropdownCloseTimers.get(group);
            if (closeTimer) {
                clearTimeout(closeTimer);
                _dropdownCloseTimers.delete(group);
            }
            const openTimer = setTimeout(() => {
                showPanel(panel);
            }, DROPDOWN_DELAY_MS);
            _dropdownOpenTimers.set(group, openTimer);
        };

        const leaveHandler = () => {
            const openTimer = _dropdownOpenTimers.get(group);
            if (openTimer) {
                clearTimeout(openTimer);
                _dropdownOpenTimers.delete(group);
            }
            const closeTimer = setTimeout(() => {
                hidePanel(panel);
            }, DROPDOWN_DELAY_MS);
            _dropdownCloseTimers.set(group, closeTimer);
        };

        hidePanel(panel);

        group.addEventListener('mouseenter', enterHandler);
        group.addEventListener('mouseleave', leaveHandler);

        _dropdownHoverHandlers.push({ element: group, panel, enterHandler, leaveHandler });
    });

    // Update year
    const currentYearElement = document.getElementById('current-year');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }

    // Active link
    updateActiveLink();

    // Sidebar groups (collapsible menus) — BEM selectors
    _sidebarGroupHandlers.forEach(({ element, handler }) => {
        if (element && handler) {
            element.removeEventListener('click', handler);
        }
    });
    _sidebarGroupHandlers = [];

    const currentPath = window.location.pathname;

    const sidebarGroups = document.querySelectorAll('.sidebar__group');

    sidebarGroups.forEach((group) => {
        const trigger = group.querySelector('.sidebar__group-trigger');
        const menu = group.querySelector('.sidebar__group-menu');
        const arrow = group.querySelector('.sidebar__group-arrow');

        if (!trigger || !menu) return;

        // Check if any submenu link matches current route
        const submenuLinks = menu.querySelectorAll('[data-link]');
        let shouldExpand = false;

        submenuLinks.forEach((link) => {
            const linkHref = link.getAttribute('href');
            if (linkHref && currentPath.startsWith(linkHref)) {
                shouldExpand = true;
            }
        });

        // Initialize expanded/collapsed state
        requestAnimationFrame(() => {
            if (shouldExpand || group.classList.contains('sidebar__group--expanded')) {
                group.classList.remove('sidebar__group--collapsed');
                group.classList.add('sidebar__group--expanded');
                const height = menu.scrollHeight;
                menu.style.maxHeight = height + 'px';
                menu.style.opacity = '1';
                menu.style.visibility = 'visible';
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            } else {
                group.classList.remove('sidebar__group--expanded');
                group.classList.add('sidebar__group--collapsed');
                menu.style.maxHeight = '0';
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        });

        // Click handler
        const handler = function(e) {
            // If click was on a link, allow normal navigation (SPA processes)
            if (e.target.closest('[data-link]')) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const isExpanded = group.classList.contains('sidebar__group--expanded');

            if (isExpanded) {
                group.classList.remove('sidebar__group--expanded');
                group.classList.add('sidebar__group--collapsed');
                menu.style.maxHeight = '0';
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            } else {
                group.classList.remove('sidebar__group--collapsed');
                group.classList.add('sidebar__group--expanded');
                const height = menu.scrollHeight;
                menu.style.maxHeight = height + 'px';
                menu.style.opacity = '1';
                menu.style.visibility = 'visible';
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        };

        trigger.addEventListener('click', handler);
        _sidebarGroupHandlers.push({ element: trigger, handler });
    });

    // User menu (collapsible) — BEM selectors
    const userWrapper = document.querySelector('.sidebar__user--collapsed, .sidebar__user--expanded');
    if (userWrapper) {
        const userTrigger = userWrapper.querySelector('.sidebar__user-trigger');
        const userMenu = userWrapper.querySelector('.sidebar__user-menu');
        const userArrow = userWrapper.querySelector('.sidebar__group-arrow');

        if (_sidebarUserHandler && userTrigger) {
            userTrigger.removeEventListener('click', _sidebarUserHandler);
            _sidebarUserHandler = null;
        }

        if (userTrigger && userMenu) {
            // Check if any user menu link matches current route
            const userLinks = userMenu.querySelectorAll('[data-link]');
            let shouldExpandUser = false;

            userLinks.forEach((link) => {
                const linkHref = link.getAttribute('href');
                if (linkHref && currentPath.startsWith(linkHref)) {
                    shouldExpandUser = true;
                }
            });

            // Initialize state
            requestAnimationFrame(() => {
                if (shouldExpandUser || userWrapper.classList.contains('sidebar__user--expanded')) {
                    userWrapper.classList.remove('sidebar__user--collapsed');
                    userWrapper.classList.add('sidebar__user--expanded');
                    const height = userMenu.scrollHeight;
                    userMenu.style.maxHeight = height + 'px';
                    userMenu.style.opacity = '1';
                    userMenu.style.visibility = 'visible';
                    if (userArrow) {
                        userArrow.style.transform = 'rotate(180deg)';
                    }
                } else {
                    userWrapper.classList.remove('sidebar__user--expanded');
                    userWrapper.classList.add('sidebar__user--collapsed');
                    userMenu.style.maxHeight = '0';
                    userMenu.style.opacity = '0';
                    userMenu.style.visibility = 'hidden';
                    if (userArrow) {
                        userArrow.style.transform = 'rotate(0deg)';
                    }
                }
            });

            _sidebarUserHandler = function(e) {
                if (e.target.closest('[data-link]')) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                const isExpanded = userWrapper.classList.contains('sidebar__user--expanded');

                if (isExpanded) {
                    userWrapper.classList.remove('sidebar__user--expanded');
                    userWrapper.classList.add('sidebar__user--collapsed');
                    userMenu.style.maxHeight = '0';
                    userMenu.style.opacity = '0';
                    userMenu.style.visibility = 'hidden';
                    if (userArrow) {
                        userArrow.style.transform = 'rotate(0deg)';
                    }
                } else {
                    userWrapper.classList.remove('sidebar__user--collapsed');
                    userWrapper.classList.add('sidebar__user--expanded');
                    const height = userMenu.scrollHeight;
                    userMenu.style.maxHeight = height + 'px';
                    userMenu.style.opacity = '1';
                    userMenu.style.visibility = 'visible';
                    if (userArrow) {
                        userArrow.style.transform = 'rotate(180deg)';
                    }
                }
            };

            userTrigger.addEventListener('click', _sidebarUserHandler);
        }
    }

    // Sidebar toggle (desktop) - collapse/expand
    const sidebarToggleBtn = document.getElementById('sidebar-collapse-btn');

    // Restore state from localStorage
    const restoreSidebarState = () => {
        if (!sidebar) return;
        const savedState = localStorage.getItem('sidebar-collapsed');
        const shouldCollapse = savedState === 'true';
        applySidebarState(shouldCollapse);
    };

    restoreSidebarState();

    // Toggle handler
    if (sidebarToggleBtn && sidebar) {
        if (_sidebarToggleHandler) {
            sidebarToggleBtn.removeEventListener('click', _sidebarToggleHandler);
            _sidebarToggleHandler = null;
        }

        _sidebarToggleHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (window.toggleSidebar && typeof window.toggleSidebar === 'function') {
                window.toggleSidebar();
            }
        };

        sidebarToggleBtn.addEventListener('click', _sidebarToggleHandler);
    }

    _layoutInitialized = true;
}

function resetLayout() {
    // Ensure no scroll lock from drawer/overlays after navigation
    document.body.classList.remove('overflow-hidden');

    // Close mobile-menu (landing page safety net)
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenu) {
        mobileMenu.classList.add('hidden');
        mobileMenu.classList.remove('flex');
    }

    // Close sidebar drawer on mobile (safety net)
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    if (sidebar) {
        sidebar.classList.remove('sidebar--open');
    }
    if (sidebarOverlay) {
        sidebarOverlay.classList.remove('sidebar__overlay--visible');
        sidebarOverlay.classList.add('hidden');
    }

    if (_mobileMenuHandler) {
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.removeEventListener('click', _mobileMenuHandler);
        }
        _mobileMenuHandler = null;
    }
    if (_mobileMenuLinkHandler) {
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileMenu) {
            mobileMenu.removeEventListener('click', _mobileMenuLinkHandler);
        }
        _mobileMenuLinkHandler = null;
    }
    if (_sidebarOpenHandler) {
        const sidebarOpenBtn = document.getElementById('sidebar-open-btn');
        if (sidebarOpenBtn) {
            sidebarOpenBtn.removeEventListener('click', _sidebarOpenHandler);
        }
        _sidebarOpenHandler = null;
    }
    if (_sidebarCloseHandler) {
        const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
        if (sidebarCloseBtn) {
            sidebarCloseBtn.removeEventListener('click', _sidebarCloseHandler);
        }
        _sidebarCloseHandler = null;
    }
    if (_sidebarOverlayHandler) {
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        if (sidebarOverlay) {
            sidebarOverlay.removeEventListener('click', _sidebarOverlayHandler);
        }
        _sidebarOverlayHandler = null;
    }
    if (_sidebarLinkClickHandler) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.removeEventListener('click', _sidebarLinkClickHandler);
        }
        _sidebarLinkClickHandler = null;
    }
    if (_dropdownClickHandler) {
        document.removeEventListener('click', _dropdownClickHandler);
        _dropdownClickHandler = null;
    }

    if (_dropdownHoverHandlers.length) {
        _dropdownHoverHandlers.forEach(({ element, panel, enterHandler, leaveHandler }) => {
            try {
                element.removeEventListener('mouseenter', enterHandler);
                element.removeEventListener('mouseleave', leaveHandler);
                if (panel) {
                    panel.classList.add('opacity-0', 'invisible', 'pointer-events-none', 'translate-y-2');
                    panel.classList.remove('opacity-100', 'visible', 'pointer-events-auto', 'translate-y-0');
                }
                const openTimer = _dropdownOpenTimers.get(element);
                if (openTimer) {
                    clearTimeout(openTimer);
                    _dropdownOpenTimers.delete(element);
                }
                const closeTimer = _dropdownCloseTimers.get(element);
                if (closeTimer) {
                    clearTimeout(closeTimer);
                    _dropdownCloseTimers.delete(element);
                }
            } catch (e) {
                // ignore
            }
        });
        _dropdownHoverHandlers = [];
    }
    if (_sidebarGroupHandlers.length) {
        _sidebarGroupHandlers.forEach(({ element, handler }) => {
            if (element && handler) {
                element.removeEventListener('click', handler);
            }
        });
        _sidebarGroupHandlers = [];
    }
    if (_sidebarUserHandler) {
        const userTrigger = document.querySelector('.sidebar__user-trigger');
        if (userTrigger) {
            userTrigger.removeEventListener('click', _sidebarUserHandler);
        }
        _sidebarUserHandler = null;
    }
    if (_sidebarToggleHandler) {
        const sidebarToggleBtn = document.getElementById('sidebar-collapse-btn');
        if (sidebarToggleBtn) {
            sidebarToggleBtn.removeEventListener('click', _sidebarToggleHandler);
        }
        _sidebarToggleHandler = null;
    }
    _layoutInitialized = false;
}

// Global sidebar toggle function
window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const isDesktop = window.matchMedia('(min-width: 768px)').matches;

    if (!sidebar) {
        console.warn('Sidebar not found');
        return;
    }

    // Mobile: toggle drawer
    if (!isDesktop) {
        const isOpen = sidebar.classList.contains('sidebar--open');

        if (isOpen) {
            sidebar.classList.remove('sidebar--open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('sidebar__overlay--visible');
                sidebarOverlay.classList.add('hidden');
            }
            document.body.classList.remove('overflow-hidden');
        } else {
            sidebar.classList.add('sidebar--open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('sidebar__overlay--visible');
                sidebarOverlay.classList.remove('hidden');
            }
            document.body.classList.add('overflow-hidden');
        }
        return;
    }

    // Desktop: reset mobile drawer state and toggle collapse/expand
    sidebar.classList.remove('sidebar--open');
    if (sidebarOverlay) {
        sidebarOverlay.classList.remove('sidebar__overlay--visible');
        sidebarOverlay.classList.add('hidden');
    }
    document.body.classList.remove('overflow-hidden');

    const isCollapsed = sidebar.classList.contains('sidebar--collapsed');

    applySidebarState(!isCollapsed);
};

// Initialize toggle immediately when DOM is ready (fallback)
(function initSidebarToggle() {
    function attachToggleListener() {
        const sidebarToggleBtn = document.getElementById('sidebar-collapse-btn');

        if (sidebarToggleBtn && !sidebarToggleBtn.hasAttribute('data-toggle-attached')) {
            sidebarToggleBtn.setAttribute('data-toggle-attached', 'true');

            sidebarToggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (window.toggleSidebar && typeof window.toggleSidebar === 'function') {
                    window.toggleSidebar();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachToggleListener);
    } else {
        attachToggleListener();
    }

    setTimeout(attachToggleListener, 100);
    setTimeout(attachToggleListener, 500);
})();

// Expose globally
window.resetLayout = resetLayout;
window.initLayout = initLayout;

// Update active link
function updateActiveLink() {
    const currentPath = window.location.pathname;
    const allLinks = document.querySelectorAll('[data-link]');

    // Remove active classes from all links
    allLinks.forEach(link => {
        if (link.hasAttribute('data-no-active')) {
            return;
        }
        const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');

        if (isButton) {
            link.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
        } else {
            link.classList.remove('text-blue-500', 'font-semibold');
            link.classList.add('text-gray-600');
        }
    });

    // Add active classes to current link
    allLinks.forEach(link => {
        if (link.hasAttribute('data-no-active')) {
            return;
        }
        if (link.getAttribute('href') === currentPath) {
            const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');

            if (isButton) {
                link.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                link.classList.remove('text-gray-600');
                link.classList.add('text-blue-500');
            }
        }
    });
}

// Called by SPA when page changes
function setActiveLink(path) {
    const allLinks = document.querySelectorAll('[data-link]');

    allLinks.forEach(link => {
        if (link.hasAttribute('data-no-active')) {
            return;
        }
        const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');

        if (isButton) {
            link.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
        } else {
            link.classList.remove('text-blue-500', 'font-semibold');
            link.classList.add('text-gray-600');
        }
    });

    allLinks.forEach(link => {
        if (link.hasAttribute('data-no-active')) {
            return;
        }
        const linkHref = link.getAttribute('href');
        if (linkHref && path.startsWith(linkHref)) {
            const isButton = link.classList.contains('btn-accent') || link.classList.contains('btn-primary') || link.classList.contains('btn-secondary');

            if (isButton) {
                link.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
            } else {
                link.classList.remove('text-gray-600');
                link.classList.add('text-blue-500');
            }

            // If link is inside a submenu, expand the parent group
            const groupMenu = link.closest('.sidebar__group-menu');
            if (groupMenu) {
                const group = groupMenu.closest('.sidebar__group');
                if (group && group.classList.contains('sidebar__group--collapsed')) {
                    const arrow = group.querySelector('.sidebar__group-arrow');

                    group.classList.remove('sidebar__group--collapsed');
                    group.classList.add('sidebar__group--expanded');
                    const height = groupMenu.scrollHeight;
                    groupMenu.style.maxHeight = height + 'px';
                    groupMenu.style.opacity = '1';
                    groupMenu.style.visibility = 'visible';
                    if (arrow) {
                        arrow.style.transform = 'rotate(180deg)';
                    }
                }
            }

            // If link is inside user menu, expand it
            const userMenu = link.closest('.sidebar__user-menu');
            if (userMenu) {
                const userWrapper = userMenu.closest('.sidebar__user--collapsed');
                if (userWrapper) {
                    const arrow = userWrapper.querySelector('.sidebar__group-arrow');

                    userWrapper.classList.remove('sidebar__user--collapsed');
                    userWrapper.classList.add('sidebar__user--expanded');
                    const height = userMenu.scrollHeight;
                    userMenu.style.maxHeight = height + 'px';
                    userMenu.style.opacity = '1';
                    userMenu.style.visibility = 'visible';
                    if (arrow) {
                        arrow.style.transform = 'rotate(180deg)';
                    }
                }
            }
        }
    });
}

// Initialize on first load
document.addEventListener('DOMContentLoaded', () => {
    try {
        initLayout();
    } catch (e) {
        // ignore
    }
});
