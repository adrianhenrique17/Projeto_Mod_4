document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');

    forms.forEach((form) => {
        if (!form.querySelector('[name="preco_original"]') || !form.querySelector('[name="preco_promocional"]')) {
            return;
        }

        form.addEventListener('submit', (event) => {
            const original = Number(form.elements.preco_original.value || 0);
            const promotional = Number(form.elements.preco_promocional.value || 0);
            if (original > 0 && promotional >= original) {
                event.preventDefault();
                window.alert('O preço promocional deve ser menor que o preço original.');
            }
        });
    });

    const filterToggle = document.querySelector('[data-filter-toggle]');
    const filterDrawer = document.getElementById('filterDrawer');

    if (filterToggle && filterDrawer) {
        filterToggle.addEventListener('click', () => {
            const isOpen = filterDrawer.classList.toggle('is-open');
            filterToggle.textContent = isOpen ? 'Ocultar filtros' : 'Filtros';
        });
    }

    const commentForm = document.getElementById('comment-form');
    if (!commentForm) {
        return;
    }

    commentForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const textarea = commentForm.querySelector('textarea[name="texto"]');
        const text = (textarea?.value || '').trim();
        if (!text) {
            return;
        }

        const formData = new FormData(commentForm);
        const params = new URLSearchParams();

        for (const [name, value] of formData.entries()) {
            params.append(name, String(value));
        }

        try {
            const response = await fetch('index.php?action=comentar', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: params.toString(),
            });

            const rawText = await response.text();
            let data;
            try {
                data = rawText ? JSON.parse(rawText) : {};
            } catch (error) {
                data = { success: false, message: rawText || 'Não foi possível publicar o comentário.' };
            }

            if (!response.ok || !data.success) {
                window.alert(data.message || 'Não foi possível publicar o comentário.');
                return;
            }

            const list = document.querySelector('.comment-list');
            if (!list) {
                window.location.reload();
                return;
            }

            const item = document.createElement('li');
            const author = document.createElement('strong');
            const textNode = document.createElement('p');

            author.textContent = data.nome_usuario;
            textNode.textContent = data.texto;
            item.appendChild(author);
            item.appendChild(textNode);
            list.appendChild(item);

            commentForm.reset();
        } catch (error) {
            window.alert('Não foi possível publicar o comentário. Tente novamente.');
        }
    });
});
