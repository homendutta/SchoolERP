
    document.querySelectorAll('img').forEach(img => {

        // Skip icons/logos if needed
        if (img.classList.contains('no-lazy')) {
            img.classList.add('img-loaded');
            return;
        }

        // If already cached
        if (img.complete) {
            img.classList.add('img-loaded');
        } else {
            img.addEventListener('load', () => {
                img.classList.add('img-loaded');
            });
        }
    });



    function initGlobalSearch() {
        const searchModal = document.getElementById('globalSearchModal');
        const searchInput = document.getElementById('globalSearchInput');
        const resultsList = document.getElementById('searchResultsList');
        const searchStatus = document.getElementById('searchStatus');

        if (!searchModal || searchModal.dataset.initialized === "true") return;
        searchModal.dataset.initialized = "true";

        let timeoutId;

        function showStatus(html) {
            searchStatus.innerHTML = html;
            searchStatus.style.display = 'block';
        }

        // 1. FIX: Purge any existing backdrops RIGHT BEFORE the modal opens
        searchModal.addEventListener('show.bs.modal', () => {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
        });

        searchModal.addEventListener('shown.bs.modal', () => {
            searchInput.focus();
            
            // 2. FIX: If Bootstrap spawned duplicates during the opening animation, kill them all except the last one
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                for (let i = 0; i < backdrops.length - 1; i++) {
                    backdrops[i].remove();
                }
            }
        });

        searchModal.addEventListener('hidden.bs.modal', () => {
            searchInput.value = '';
            resultsList.innerHTML = '';
            showStatus('<i class="bi bi-keyboard fs-2 mb-2 d-block"></i>Start typing to explore notifications, pages, events <br>or any content on the university website.');
            
            // 3. FIX: Aggressively wipe the slate clean when closed
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });

        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();

            if (query.length === 0) {
                resultsList.innerHTML = '';
                showStatus('<i class="bi bi-keyboard fs-2 mb-2 d-block"></i>Start typing to explore notifications, pages, events <br>or any content on the university website.');
                clearTimeout(timeoutId);
                return;
            }

            showStatus(`
            <div class="d-flex justify-content-center align-items-center text-muted mb-2 mt-4">
                <span class="spinner-grow spinner-grow-sm me-1" style="animation-delay: 0s" role="status"></span>
                <span class="spinner-grow spinner-grow-sm me-1" style="animation-delay: 0.2s" role="status"></span>
                <span class="spinner-grow spinner-grow-sm" style="animation-delay: 0.4s" role="status"></span>
            </div>
            <div class="small text-muted">Waiting to search...</div>
        `);
            resultsList.innerHTML = '';

            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                fetchSearchData(query);
            }, 400); // 400ms debounce
        });

        function fetchSearchData(query) {

            showStatus(`
            <div class="spinner-border text-primary border-2 mt-4 mb-2" role="status"></div>
            <div class="small text-muted">Searching database...</div>
        `);

            const url = 'https://nlu.ac.in/searchGlobal?q=' + encodeURIComponent(query);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    searchStatus.style.display = 'none';
                    resultsList.innerHTML = '';

                    if (data.length === 0) {
                        showStatus('<i class="bi bi-search fs-3 d-block mb-2"></i>No results found for "<strong>' + query + '</strong>"');
                        return;
                    }

                    data.forEach(item => {
                        let iconClass = 'bi-file-text';
                        let bgClass = 'bg-light text-dark';

                        if (item.result_type === 'Event') {
                            iconClass = 'bi-calendar-event';
                            bgClass = 'bg-success bg-opacity-10 text-success';
                        } else if (item.result_type === 'Department') {
                            iconClass = 'bi-building';
                            bgClass = 'bg-primary bg-opacity-10 text-primary';
                        } else if (item.result_type === 'Notification') {
                            iconClass = 'bi-bell';
                            bgClass = 'bg-warning bg-opacity-10 text-warning';
                        }

                        let extraInfo = item.extra_info ?
                            `<div class="small text-muted text-truncate mt-1 w-75">${item.extra_info}</div>` :
                            '';

                        const html = `
                        <a href="https://nlu.ac.in/${item.final_url}" class="list-group-item list-group-item-action d-flex align-items-center" data-turbo="false">
                            <div class="result-icon ${bgClass}">
                                <i class="bi ${iconClass}"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="mb-0 text-dark text-truncate">${item.title}</strong>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill border ms-2">${item.result_type}</span>
                                </div>
                                ${extraInfo}
                            </div>
                        </a>
                    `;
                        resultsList.insertAdjacentHTML('beforeend', html);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStatus('<div class="text-danger mt-4">An error occurred. Please try again.</div>');
                });
        }
    }

    // Initialize on both standard load and turbo transitions
    document.addEventListener('DOMContentLoaded', initGlobalSearch);
    document.addEventListener('turbo:load', initGlobalSearch);

    // 4. FIX: Still keep the before-cache cleanup to prevent Turbo from saving the state
    if (!document.documentElement.dataset.turboModalFixInitialized) {
        document.documentElement.dataset.turboModalFixInitialized = "true";
        document.addEventListener("turbo:before-cache", function() {
            const activeModal = document.querySelector('.modal.show');
            if (activeModal && typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(activeModal);
                if (modalInstance) { modalInstance.hide(); }
            }
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
