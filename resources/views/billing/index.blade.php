<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Billing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 24px;
            color: #222;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        header {
            background: #243447;
            color: white;
            padding: 20px 24px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .panel {
            background: white;
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px #0000000d;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select,
        button {
            box-sizing: border-box;
            width: 100%;
            padding: 10px;
            border: 1px solid #ccd2da;
            border-radius: 5px;
            font-size: 15px;
        }

        button {
            background: #1769aa;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #12568d;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .totals {
            max-width: 360px;
            margin-left: auto;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .grand-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #243447;
            margin-top: 8px;
            padding-top: 14px;
        }

        .muted {
            color: #667085;
        }

        .error {
            color: #b42318;
            white-space: pre-wrap;
        }

        .success {
            color: #067647;
            white-space: pre-wrap;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        .actions button {
            flex: 1;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            .panel {
                padding: 16px;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>Store Billing</h1>
            <p>Create customer orders and generate bills</p>
        </header>

        <section class="panel">
            <h2>Customer Details</h2>
            <div class="grid">
                <div>
                    <label for="customerName">Customer name</label>
                    <input id="customerName" placeholder="Enter customer name">
                </div>
                <div>
                    <label for="customerEmail">Customer email</label>
                    <input id="customerEmail" type="email" placeholder="customer@example.com">
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Products</h2>
            <div class="grid">
                <div>
                    <label for="productSelect">Select product</label>
                    <select id="productSelect">
                        <option value="">Loading products...</option>
                    </select>
                </div>
                <div>
                    <label for="productQuantity">Quantity</label>
                    <input id="productQuantity" type="number" min="1" value="1">
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button type="button" id="addProduct">Add product</button>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit price</th>
                            <th>Tax</th>
                            <th>Line total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        <tr>
                            <td colspan="6" class="muted">No products added yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Bill Summary</h2>
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal</span><strong id="subtotal">₹0.00</strong>
                </div>
                <div class="total-row">
                    <span>Tax</span><strong id="taxTotal">₹0.00</strong>
                </div>
                <div class="total-row grand-total">
                    <span>Grand total</span><strong id="grandTotal">₹0.00</strong>
                </div>
                <div class="total-row">
                    <label for="amountGiven">Amount given (₹)</label>
                    <input id="amountGiven" type="number" min="0" step="0.01" value="0">
                </div>
                <div class="total-row">
                    <span>Balance to return</span><strong id="balance">₹0.00</strong>
                </div>
            </div>

            <div class="actions">
                <button type="button" id="generateBill">Generate Bill</button>
            </div>
            <p id="message" role="status"></p>
        </section>

        <section class="panel">
            <h2>Low Stock Alerts</h2>
            <p class="muted">Products with stock below 5 units.</p>
            <ul id="lowStockList">
                <li>Loading...</li>
            </ul>
        </section>

        <section class="panel" id="billPanel" hidden>
            <h2>Generated Bill</h2>
            <div id="billContent"></div>
            <button type="button" id="printBill">Print Bill</button>
        </section>
    </div>

    <script>
        const productSelect = document.getElementById('productSelect');
        const productQuantity = document.getElementById('productQuantity');
        const cartBody = document.getElementById('cartBody');
        const message = document.getElementById('message');

        let products = [];
        let cart = [];

        const money = value => '₹' + Number(value).toFixed(2);

        async function api(url, options = {}) {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    ...(options.headers || {})
                }
            });

            const result = await response.json();

            if (!response.ok) {
                const errors = result.errors ?
                    Object.values(result.errors).flat().join('\n') :
                    result.message || 'Request failed.';
                throw new Error(errors);
            }

            return result;
        }

        async function loadProducts() {
            try {
                const result = await api('/api/products');
                products = result.data;

                productSelect.innerHTML = '<option value="">Choose a product</option>';

                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent =
                        `${product.name} — ${money(product.price)} — Stock: ${product.stock_quantity}`;
                    productSelect.appendChild(option);
                });

                await loadLowStock();
            } catch (error) {
                message.className = 'error';
                message.textContent = 'Could not load products: ' + error.message;
                productSelect.innerHTML = '<option value="">Unable to load products</option>';
            }
        }

        async function loadLowStock() {
            const result = await api('/api/products/low-stock?threshold=5');
            const list = document.getElementById('lowStockList');
            list.replaceChildren();

            if (result.data.length === 0) {
                list.innerHTML = '<li>No low-stock products.</li>';
                return;
            }

            result.data.forEach(product => {
                const item = document.createElement('li');
                item.textContent = `${product.name}: only ${product.stock_quantity} left`;
                list.appendChild(item);
            });
        }

        function calculateTotals() {
            let subtotal = 0;
            let taxTotal = 0;

            cart.forEach(item => {
                const lineSubtotal = item.price * item.quantity;
                const lineTax = Math.round(lineSubtotal * item.tax_percentage) / 100;
                subtotal += lineSubtotal;
                taxTotal += lineTax;
            });

            subtotal = Math.round(subtotal * 100) / 100;
            taxTotal = Math.round(taxTotal * 100) / 100;

            const grandTotal = Math.round((subtotal + taxTotal) * 100) / 100;
            const amountGiven = Number(document.getElementById('amountGiven').value) || 0;
            const balance = Math.max(0, amountGiven - grandTotal);

            document.getElementById('subtotal').textContent = money(subtotal);
            document.getElementById('taxTotal').textContent = money(taxTotal);
            document.getElementById('grandTotal').textContent = money(grandTotal);
            document.getElementById('balance').textContent = money(balance);

            return {
                subtotal,
                taxTotal,
                grandTotal,
                amountGiven,
                balance
            };
        }

        function renderCart() {
            cartBody.replaceChildren();

            if (cart.length === 0) {
                const row = cartBody.insertRow();
                const cell = row.insertCell();
                cell.colSpan = 6;
                cell.className = 'muted';
                cell.textContent = 'No products added yet.';
                calculateTotals();
                return;
            }

            cart.forEach(item => {
                const row = cartBody.insertRow();

                [
                    item.name,
                    String(item.quantity),
                    money(item.price),
                    `${item.tax_percentage}%`,
                    money(item.price * item.quantity * (1 + item.tax_percentage / 100))
                ].forEach(value => {
                    row.insertCell().textContent = value;
                });

                const actionCell = row.insertCell();
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.textContent = 'Remove';
                removeButton.addEventListener('click', () => {
                    cart = cart.filter(cartItem => cartItem.id !== item.id);
                    renderCart();
                });
                actionCell.appendChild(removeButton);
            });

            calculateTotals();
        }

        document.getElementById('addProduct').addEventListener('click', () => {
            const id = Number(productSelect.value);
            const quantity = Number(productQuantity.value);
            const product = products.find(item => item.id === id);

            if (!product) {
                message.className = 'error';
                message.textContent = 'Please select a product.';
                return;
            }

            if (!Number.isInteger(quantity) || quantity < 1) {
                message.className = 'error';
                message.textContent = 'Quantity must be at least 1.';
                return;
            }

            const existing = cart.find(item => item.id === id);
            const cartQuantity = (existing?.quantity || 0) + quantity;

            if (cartQuantity > product.stock_quantity) {
                message.className = 'error';
                message.textContent = `Only ${product.stock_quantity} units of ${product.name} are available.`;
                return;
            }

            if (existing) {
                existing.quantity += quantity;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: Number(product.price),
                    tax_percentage: Number(product.tax_percentage),
                    quantity
                });
            }

            message.textContent = '';
            renderCart();
        });

        document.getElementById('amountGiven').addEventListener('input', calculateTotals);

        document.getElementById('generateBill').addEventListener('click', async () => {
            const name = document.getElementById('customerName').value.trim();
            const email = document.getElementById('customerEmail').value.trim();

            if (!name || !email || cart.length === 0) {
                message.className = 'error';
                message.textContent = 'Enter customer name, valid email, and add at least one product.';
                return;
            }

            const totals = calculateTotals();

            if (totals.amountGiven < totals.grandTotal) {
                message.className = 'error';
                message.textContent = 'Amount given must cover the grand total.';
                return;
            }

            const button = document.getElementById('generateBill');
            button.disabled = true;
            button.textContent = 'Generating...';

            try {
                const result = await api('/api/orders', {
                    method: 'POST',
                    body: JSON.stringify({
                        customer: {
                            name,
                            email
                        },
                        items: cart.map(item => ({
                            product_id: item.id,
                            quantity: item.quantity
                        }))
                    })
                });

                const order = result.data;
                const billContent = document.getElementById('billContent');
                billContent.replaceChildren();

                const heading = document.createElement('h3');
                heading.textContent = `Order #${order.id}`;
                billContent.appendChild(heading);

                const customer = document.createElement('p');
                customer.textContent = `${name} (${email})`;
                billContent.appendChild(customer);

                order.items.forEach(item => {
                    const line = document.createElement('p');
                    line.textContent =
                        `${item.product.name} × ${item.quantity} — ${money(item.line_total)}`;
                    billContent.appendChild(line);
                });

                const total = document.createElement('h3');
                total.textContent = `Grand total: ${money(order.grand_total)}`;
                billContent.appendChild(total);

                const paid = document.createElement('p');
                paid.textContent = `Amount given: ${money(totals.amountGiven)}`;
                billContent.appendChild(paid);

                const balance = document.createElement('p');
                balance.textContent = `Balance to return: ${money(totals.balance)}`;
                billContent.appendChild(balance);

                document.getElementById('billPanel').hidden = false;
                message.className = 'success';
                message.textContent = 'Order created successfully. Confirmation job queued.';

                cart = [];
                renderCart();
                await loadProducts();
            } catch (error) {
                message.className = 'error';
                message.textContent = error.message;
            } finally {
                button.disabled = false;
                button.textContent = 'Generate Bill';
            }
        });

        document.getElementById('printBill').addEventListener('click', () => window.print());

        loadProducts();
    </script>
</body>

</html>
