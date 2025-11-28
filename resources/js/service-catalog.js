const services = [];

function setView(view) {
    const grid = document.getElementById('gridView');
    const list = document.getElementById('listView');
    const gridBtn = document.getElementById('gridBtn');
    const listBtn = document.getElementById('listBtn');

    if (view === 'grid') {
        grid.style.display = 'grid';
        list.style.display = 'none';
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
    } else {
        grid.style.display = 'none';
        list.style.display = 'block';
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
    }
}

function filterServices() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const cards = document.querySelectorAll('.service-card');
    let count = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const cat = card.getAttribute('data-category');
        const matchSearch = !search || name.includes(search);
        const matchCategory = !category || cat === category;

        if (matchSearch && matchCategory) {
            card.style.display = '';
            count++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('serviceCount').textContent = count;
}

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    filterServices();
}

function showDetail(code) {
    const service = services.find(s => s.code === code);
    if (!service) return;

    document.getElementById('modalTitle').textContent = service.name;
    document.getElementById('modalContent').innerHTML = `
        <div style="line-height: 1.8;">
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px;">KODE LAYANAN</div>
                <div style="font-size: 15px;">${service.code}</div>
            </div>
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px;">DESKRIPSI</div>
                <div style="font-size: 15px;">${service.description}</div>
            </div>
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px;">MODE PELAYANAN</div>
                <div style="font-size: 15px; text-transform: capitalize;">${service.mode}</div>
            </div>
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px;">PERSYARATAN</div>
                <div style="font-size: 15px;">${service.requirements}</div>
            </div>
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px;">WAKTU PENYELESAIAN</div>
                <div style="font-size: 15px;">${service.processingTime}</div>
            </div>
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px;">BIAYA</div>
                <div style="font-size: 15px;">Rp ${parseFloat(service.fee).toLocaleString('id-ID')}</div>
            </div>
        </div>
    `;

    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function toggleAccordion(header) {
    try {
        const content = header.nextElementSibling;
        const icon = header.querySelector('.accordion-icon');
        const isCurrentlyActive = content.classList.contains('active');
        
        // Find the parent service card
        const currentCard = header.closest('.service-card');
        
        // Close all accordions in the same card
        if (currentCard) {
            const allAccordions = currentCard.querySelectorAll('.accordion-content');
            const allIcons = currentCard.querySelectorAll('.accordion-icon');
            
            allAccordions.forEach(item => item.classList.remove('active'));
            allIcons.forEach(icon => icon.classList.remove('active'));
            // Also remove active class from accordion elements
            const accordions = currentCard.querySelectorAll('.accordion-item');
            accordions.forEach(acc => acc.classList.remove('active'));
        }
        
        // If the clicked accordion wasn't active, open it
        if (!isCurrentlyActive) {
            content.classList.add('active');
            icon.classList.add('active');
            // Add active class to parent accordion element for visual effects
            header.parentElement.classList.add('active');
        }
        // If it was active, we just close it (by not adding the active class)
    } catch (error) {
        console.error('Error in toggleAccordion:', error);
    }
}

// New function for service accordion (single accordion per service)
function toggleServiceAccordion(element, serviceId) {
    try {
        const header = element.querySelector('.accordion-header');
        const content = element.querySelector('.accordion-content');
        const icon = element.querySelector('.accordion-icon');
        const isCurrentlyActive = content.classList.contains('active');
        
        // Close all service accordions
        const allServiceAccordions = document.querySelectorAll('.service-main-accordion');
        allServiceAccordions.forEach(accordion => {
            const accContent = accordion.querySelector('.accordion-content');
            const accIcon = accordion.querySelector('.accordion-icon');
            accContent.classList.remove('active');
            accIcon.classList.remove('active');
            // Remove active class from parent accordion element
            accordion.classList.remove('active');
        });
        
        // If the clicked accordion wasn't active, open it
        if (!isCurrentlyActive) {
            content.classList.add('active');
            icon.classList.add('active');
            // Add active class to parent accordion element for visual effects
            element.classList.add('active');
        }
        // If it was active, we just close it (by not adding the active class)
    } catch (error) {
        console.error('Error in toggleServiceAccordion:', error);
    }
}
