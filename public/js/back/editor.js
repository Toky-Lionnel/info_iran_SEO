'use strict';

const contentField = document.getElementById('content');

if (contentField instanceof HTMLTextAreaElement) {
    const toolbar = document.createElement('div');
    toolbar.className = 'editor-toolbar';
    toolbar.style.display = 'flex';
    toolbar.style.gap = '0.5rem';
    toolbar.style.marginBottom = '0.6rem';

    const actions = [
        { label: 'H2', open: '<h2>', close: '</h2>' },
        { label: 'H3', open: '<h3>', close: '</h3>' },
        { label: 'P', open: '<p>', close: '</p>' },
        { label: 'Strong', open: '<strong>', close: '</strong>' },
        { label: 'Quote', open: '<blockquote>', close: '</blockquote>' },
    ];

    actions.forEach((action) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn-sm';
        button.textContent = action.label;
        button.addEventListener('click', () => {
            const start = contentField.selectionStart;
            const end = contentField.selectionEnd;
            const selectedText = contentField.value.slice(start, end);
            const replacement = `${action.open}${selectedText}${action.close}`;

            contentField.setRangeText(replacement, start, end, 'end');
            contentField.focus();
        });
        toolbar.appendChild(button);
    });

    contentField.parentNode?.insertBefore(toolbar, contentField);
}
