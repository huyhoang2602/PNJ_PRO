document.addEventListener("DOMContentLoaded", function() {
    // 1. MEGA MENU ITEM HOVER SWITCH (Left Sidebar in Dropdown)
    const megaItems = document.querySelectorAll(".mega-item");
    megaItems.forEach(item => {
        item.addEventListener("mouseenter", function() {
            console.log("PNJ Mega Menu: Hovering item", this.textContent);
            const parentModal = this.closest(".mega-modal");
            if (!parentModal || window.innerWidth <= 991) return;

            // Remove active from all items in this specific modal
            parentModal.querySelectorAll(".mega-item").forEach(i => i.classList.remove("active"));
            this.classList.add("active");

            // Switch content
            const targetId = this.getAttribute("data-target");
            const targetContent = parentModal.querySelector("#" + targetId);

            if (targetContent) {
                parentModal.querySelectorAll(".mega-content").forEach(c => c.classList.remove("active"));
                targetContent.classList.add("active");
            }
        });
    });

    // 2. MOBILE MENU RE-INIT (Handle new dynamic elements)
    const mobileToggle = document.querySelector(".pnj-mobile-toggle");
    const navContainer = document.getElementById("pnj-nav-container");
    const navClose = document.querySelector(".pnj-nav-close");

    if (mobileToggle && navContainer) {
        mobileToggle.onclick = function() {
            navContainer.classList.add("active");
        };
    }

    if (navClose && navContainer) {
        navClose.onclick = function() {
            navContainer.classList.remove("active");
        };
    }

    // 3. MOBILE ACCORDION FOR TABS
    const megaTabLinks = document.querySelectorAll(".mega-parent > a");
    megaTabLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            if (window.innerWidth <= 991) {
                e.preventDefault();
                const parentLi = this.parentElement;
                
                // Toggle active state for accordion
                const wasActive = parentLi.classList.contains("active");
                document.querySelectorAll(".mega-parent").forEach(li => li.classList.remove("active"));
                
                if (!wasActive) {
                    parentLi.classList.add("active");
                }
            }
        });
    });

    // 4. SEARCH MODAL LINKING (Re-binding for new dynamic search box)
    function initSearchModalTrigger() {
        const searchInput = document.querySelector(".pnj-search input");
        const searchModal = document.getElementById("pnj-search-modal");
        const searchModalInput = document.getElementById("pnj-search-modal-input");

        if (searchInput && searchModal) {
            searchInput.onclick = function(e) {
                e.preventDefault();
                searchModal.classList.add("active");
                if (searchModalInput) {
                    setTimeout(() => { searchModalInput.focus(); }, 100);
                }
            };
        }
    }
    
    initSearchModalTrigger();

    // 5. HOVER INTENT DELAY (Optional Premium Feel)
    let megaTimeout;
    const megaParents = document.querySelectorAll(".mega-parent");
    megaParents.forEach(parent => {
        parent.addEventListener("mouseenter", () => {
            console.log("PNJ Mega Menu: Parent hover intent", parent.querySelector('a').textContent);
            clearTimeout(megaTimeout);
            megaParents.forEach(p => p.classList.remove("hover-intent"));
            parent.classList.add("hover-intent");
        });
        
        parent.addEventListener("mouseleave", () => {
            megaTimeout = setTimeout(() => {
                parent.classList.remove("hover-intent");
            }, 100);
        });
    });
});
