<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item CRUD</title>
</head>
<body>
    <h1>Item Management</h1>

    <h2>Fetch Items</h2>
    <button onclick="fetchItems()">Fetch Items</button>
    <div id="items"></div>

    <h2>Fetch by Category</h2>
    <form id="fetchByCategoryForm">
        <input type="text" id="categoryInput" placeholder="Enter Category" required>
        <button type="submit">Fetch Items by Category</button>
    </form>
    <div id="categoryResults"></div>

    <h2>Fetch by Subcategory</h2>
    <form id="fetchBySubcategoryForm">
        <input type="text" id="subcategoryInput" placeholder="Enter Subcategory" required>
        <button type="submit">Fetch Items by Subcategory</button>
    </form>
    <div id="subcategoryResults"></div>
    
    <h2>Create Item</h2>
    <form id="createItemForm" enctype="multipart/form-data">
        <input type="text" id="itemName" placeholder="Item Name" required>
        <input type="text" id="itemDescription" placeholder="Item Description" required>
        <input type="number" id="itemPriceSmall" placeholder="Item Price Small" required>
        <input type="number" id="itemPriceMedium" placeholder="Item Price Medium" required>
        <input type="number" id="itemPriceLarge" placeholder="Item Price Large" required>
        <input type="text" id="itemCategory" placeholder="Item Category" required>
        <input type="text" id="itemSubcategory" placeholder="Item Subcategory">
        <input type="file" id="itemImage" placeholder="Item Image">
        <select id="itemIsAvailable">
            <option value="1">Available</option>
            <option value="0">Not Available</option>
        </select>
        <button type="submit">Create Item</button>
    </form>
    <div id="createItemResponse"></div>

    <h2>Update Item</h2>
    <form id="updateItemForm">
        <input type="text" id="updateItemName" placeholder="Existing Item Name" required>
        <input type="text" id="newItemName" placeholder="New Item Name">
        <input type="text" id="newItemDescription" placeholder="New Description">
        <input type="number" id="newItemPriceSmall" placeholder="New Price Small">
        <input type="number" id="newItemPriceMedium" placeholder="New Price Medium">
        <input type="number" id="newItemPriceLarge" placeholder="New Price Large">
        <input type="text" id="newItemCategory" placeholder="New Category">
        <input type="text" id="newItemSubcategory" placeholder="New Subcategory">
        <input type="file" id="newItemImage" placeholder="New Image">
        <select id="newItemIsAvailable">
            <option value="1">Available</option>
            <option value="0">Not Available</option>
        </select>
        <button type="submit">Update Item</button>
    </form>
    <div id="updateItemResponse"></div>

    <h2>Delete Item</h2>
    <form id="deleteItemForm">
        <input type="text" id="deleteItemName" placeholder="Item Name to Delete" required>
        <button type="submit">Delete Item</button>
    </form>
    <div id="deleteItemResponse"></div>

    <h2>Search Items</h2>
    <form id="searchItemForm">
        <input type="text" id="searchKeyword" placeholder="Search Keyword">
        <button type="submit">Search</button>
    </form>
    <div id="searchResults"></div>

    <script>
        const apiUrl = 'http://localhost/expresso-cafe/api/items';
        const wsUrl = 'ws://localhost:8080'; // WebSocket server URL

        // WebSocket connection setup
        let socket = new WebSocket(wsUrl);
        socket.onopen = function(event) {
            console.log('WebSocket is open now.');
        };
        socket.onmessage = function(event) {
            console.log('WebSocket message received:', event.data);

            // Parse WebSocket message
            const message = JSON.parse(event.data);
            if (message.action === 'item_created' || message.action === 'item_updated' || message.action === 'item_deleted') {
                fetchItems(); // Refresh the item list on relevant events
            }
        };
        socket.onclose = function(event) {
            console.log('WebSocket is closed now.');
        };
        socket.onerror = function(error) {
            console.error('WebSocket error observed:', error);
        };

        // Fetch all items
        function fetchItems() {
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const itemsDiv = document.getElementById('items');
                    itemsDiv.innerHTML = '<h3>Items:</h3>';
                    data.forEach(item => {
                        itemsDiv.innerHTML += `<p>${item.name} - ${item.description} - Small: $${item.price_small}, Medium: $${item.price_medium}, Large: $${item.price_large} - Category: ${item.category} - Subcategory: ${item.subcategory} - Available: ${item.is_available} - Image: <img src="${item.image_url}" alt="${item.name}" width="50"></p>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    document.getElementById('items').innerHTML = '<p>Error fetching items.</p>';
                });
        }

        // Fetch items by category
        document.getElementById('fetchByCategoryForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const category = document.getElementById('categoryInput').value;

            fetch(`${apiUrl}?category=${encodeURIComponent(category)}`)
                .then(response => response.json())
                .then(data => {
                    const categoryResultsDiv = document.getElementById('categoryResults');
                    categoryResultsDiv.innerHTML = `<h3>Items in Category: ${category}</h3>`;
                    data.forEach(item => {
                        categoryResultsDiv.innerHTML += `
                            <p>
                                ${item.name} - ${item.description} - Small: $${item.price_small}, Medium: $${item.price_medium}, Large: $${item.price_large} 
                                - Image: <img src="${item.image_url}" alt="${item.name}" width="50">
                            </p>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching items by category:', error);
                    document.getElementById('categoryResults').innerHTML = '<p>Error fetching items by category.</p>';
                });
        });


        // Fetch items by subcategory
        document.getElementById('fetchBySubcategoryForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const subcategory = document.getElementById('subcategoryInput').value;

            fetch(`${apiUrl}?subcategory=${encodeURIComponent(subcategory)}`)
                .then(response => response.json())
                .then(data => {
                    const subcategoryResultsDiv = document.getElementById('subcategoryResults');
                    subcategoryResultsDiv.innerHTML = `<h3>Items in Subcategory: ${subcategory}</h3>`;
                    data.forEach(item => {
                        subcategoryResultsDiv.innerHTML += `
                            <p>
                                ${item.name} - ${item.description} - Small: $${item.price_small}, Medium: $${item.price_medium}, Large: $${item.price_large} 
                                - Image: <img src="${item.image_url}" alt="${item.name}" width="50">
                            </p>`;
                    });
                })
                .catch(error => {
                    console.error('Error fetching items by subcategory:', error);
                    document.getElementById('subcategoryResults').innerHTML = '<p>Error fetching items by subcategory.</p>';
                });
        });

        // Create a new item
        document.getElementById('createItemForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData();
            formData.append('name', document.getElementById('itemName').value);
            formData.append('description', document.getElementById('itemDescription').value);
            formData.append('price_small', document.getElementById('itemPriceSmall').value);
            formData.append('price_medium', document.getElementById('itemPriceMedium').value);
            formData.append('price_large', document.getElementById('itemPriceLarge').value);
            formData.append('category', document.getElementById('itemCategory').value);
            formData.append('subcategory', document.getElementById('itemSubcategory').value);
            formData.append('is_available', document.getElementById('itemIsAvailable').value);
            formData.append('image', document.getElementById('itemImage').files[0]);

            fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('createItemResponse').innerHTML = '<p>Item created successfully.</p>';
                    fetchItems(); // Refresh item list
                })
                .catch(error => {
                    console.error('Error creating item:', error);
                    document.getElementById('createItemResponse').innerHTML = '<p>Error creating item.</p>';
                });
        });

        // Update an item
        document.getElementById('updateItemForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData();
            formData.append('name', document.getElementById('updateItemName').value);
            formData.append('new_name', document.getElementById('newItemName').value);
            formData.append('description', document.getElementById('newItemDescription').value);
            formData.append('price_small', document.getElementById('newItemPriceSmall').value);
            formData.append('price_medium', document.getElementById('newItemPriceMedium').value);
            formData.append('price_large', document.getElementById('newItemPriceLarge').value);
            formData.append('category', document.getElementById('newItemCategory').value);
            formData.append('subcategory', document.getElementById('newItemSubcategory').value);
            formData.append('is_available', document.getElementById('newItemIsAvailable').value);
            formData.append('image', document.getElementById('newItemImage').files[0]);

            fetch(apiUrl, {
                method: 'PUT',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('updateItemResponse').innerHTML = '<p>Item updated successfully.</p>';
                    fetchItems(); // Refresh item list
                })
                .catch(error => {
                    console.error('Error updating item:', error);
                    document.getElementById('updateItemResponse').innerHTML = '<p>Error updating item.</p>';
                });
        });

        // Delete an item
        document.getElementById('deleteItemForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const itemName = document.getElementById('deleteItemName').value;
            fetch(apiUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `name=${encodeURIComponent(itemName)}`
            })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('deleteItemResponse').innerHTML = '<p>Item deleted successfully.</p>';
                    fetchItems(); // Refresh item list
                })
                .catch(error => {
                    console.error('Error deleting item:', error);
                    document.getElementById('deleteItemResponse').innerHTML = '<p>Error deleting item.</p>';
                });
        });

        // Search items
        document.getElementById('searchItemForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const keyword = document.getElementById('searchKeyword').value;
            fetch(`${apiUrl}?search=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    const searchResultsDiv = document.getElementById('searchResults');
                    searchResultsDiv.innerHTML = '<h3>Search Results:</h3>';
                    data.forEach(item => {
                        searchResultsDiv.innerHTML += `<p>${item.name} - ${item.description} - Small: $${item.price_small}, Medium: $${item.price_medium}, Large: $${item.price_large} - Category: ${item.category} - Subcategory: ${item.subcategory} - Available: ${item.is_available} - Image: <img src="${item.image_url}" alt="${item.name}" width="50"></p>`;
                    });
                })
                .catch(error => {
                    console.error('Error searching items:', error);
                    document.getElementById('searchResults').innerHTML = '<p>Error searching items.</p>';
                });
        });
    </script>
</body>
</html>
