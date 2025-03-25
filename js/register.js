document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch('', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        showModal(data.message);
        if (data.success) {
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        }
    });
});

function showModal(message) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
            <p class="text-lg font-semibold mb-4">${message}</p>
            <button onclick="closeModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">OK</button>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.querySelector('.fixed.inset-0');
    if (modal) {
        modal.remove();
    }
}
