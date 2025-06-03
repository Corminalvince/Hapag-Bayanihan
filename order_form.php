<form method="POST" action="place_order.php">
    <!-- Example inputs, adjust based on your setup -->
    <label for="food_id">Food ID:</label>
    <input type="number" name="food_id" required><br>

    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" required><br>

    <label for="location">Delivery Location:</label>
    <input type="text" name="location" required><br>

    <label for="payment_method">Payment Method:</label>
    <select name="payment_method" id="payment_method" required onchange="toggleAmountField()">
        <option value="">--Select--</option>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="GCash">GCash</option>
    </select><br>

    <div id="paymentAmountWrapper" style="display: none;">
        <label for="payment_amount">Payment Amount (₱):</label>
        <input type="number" step="0.01" name="payment_amount" id="payment_amount" min="1">
    </div>

    <button type="submit">Place Order</button>
</form>

<script>
function toggleAmountField() {
    const method = document.getElementById("payment_method").value;
    const wrapper = document.getElementById("paymentAmountWrapper");

    if (method === "Cash on Delivery") {
        wrapper.style.display = "block";
    } else {
        wrapper.style.display = "none";
        document.getElementById("payment_amount").value = "";
    }
}
</script>
