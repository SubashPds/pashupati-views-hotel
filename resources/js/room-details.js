document.querySelectorAll('[data-room-details]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.roomDetails);
        if (!dialog) return;
        dialog.dataset.previousOverflow = document.body.style.overflow;
        dialog.showModal();
        dialog.querySelector('[data-room-body]').scrollTop = 0;
        document.body.style.overflow = 'hidden';
    });
});

document.querySelectorAll('.room-details-dialog').forEach((dialog) => {
    dialog.querySelector('[data-close-room]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        if (!document.querySelector('dialog[open]')) {
            document.body.style.overflow = dialog.dataset.previousOverflow || '';
        }
    });

});

document.querySelectorAll('[data-book-room]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('dialog')?.close();
        window.openBooking(button.dataset.bookRoom);
    });
});
