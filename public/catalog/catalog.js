// Load categories
async function loadCategories() {
    try {
        const res = await apiFetch('/api/catalog/categories');

        if (!res) return;

        if (res.status === 200) {
            const body = await res.json();
            const category = document.getElementById('category');

            for (let c of body.data) {
                const option = document.createElement('option');
                option.value = c.id;
                option.textContent = c.name;
                category.appendChild(option);
            }
        } else {
            showBanner('Error loading categories.');
        }
    } catch (error) {
        showBanner('Error loading categories.');
    }
}

document.addEventListener('DOMContentLoaded', loadCategories);

// Load products
async function loadCatalogProducts() {
    const list = document.getElementById('catalog-products');

    try {
const search = document.getElementById('search').value.trim();
const category = document.getElementById('category').value;

const url = '/api/catalog/products?q=' +
    encodeURIComponent(search) + '&category_id=' + category;

const res = await apiFetch(url);

        if (!res) return;

        if (res.status === 200) {
            const body = await res.json();

            list.innerHTML = '';

            if (!body.data || body.data.length === 0) {
                list.innerHTML = '<p>No products found.</p>';
                return;
            }

            body.data.forEach(product => {
                const card = document.createElement('div');
                card.className = 'card';

                card.innerHTML = `
                    <h3>${escapeHtml(product.name)}</h3>
                    <p>Price: $${Number(product.price).toFixed(2)}</p>
                    <p>Stock: ${product.stock}</p>
                    <button type="button" data-id="${product.id}">
                        View Details
                    </button>
                `;
const button = card.querySelector('button');

button.addEventListener('click', function() {
    loadProductDetails(product.id);
});
                list.appendChild(card);
            });
        } else {
            showBanner('Error loading products.');
        }
    } catch (error) {
        showBanner('Error loading products.');
    }
}

document.addEventListener('DOMContentLoaded', loadCatalogProducts);
// Search products
document.getElementById('search-form').addEventListener('submit', function(e) {
    e.preventDefault();
    hideBanner();
    loadCatalogProducts();
});

// Filter products
document.getElementById('category').addEventListener('change', function() {
    hideBanner();
    loadCatalogProducts();
});
// View product details
async function loadProductDetails(id) {
    const details = document.getElementById('product-details');

    hideBanner();
    details.innerHTML = '';

    try {
        const res = await apiFetch('/api/catalog/products/' + id);

        if (!res) return;

        if (res.status === 200) {
            const product = await res.json();

            details.innerHTML = `
                <h3>${escapeHtml(product.name)}</h3>
                <p>ID: ${product.id}</p>
                <p>Category ID: ${product.category_id}</p>
                <p>Price: $${Number(product.price).toFixed(2)}</p>
                <p>Stock: ${product.stock}</p>
            `;
        } else if (res.status === 404) {
            details.textContent = 'Product not found.';
        } else {
            showBanner('Error loading product details.');
        }
    } catch (error) {
        showBanner('Error loading product details.');
    }
}