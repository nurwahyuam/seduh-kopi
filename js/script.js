let keranjang = JSON.parse(localStorage.getItem("keranjang")) || [];

updateKeranjangDisplay();

document.querySelectorAll(".add-to-cart").forEach((button) => {
  button.addEventListener("click", function () {
    const id = this.getAttribute("data-id");
    const name = this.getAttribute("data-name");
    const price = parseFloat(this.getAttribute("data-price"));

    const existingItem = keranjang.find((item) => item.id === id);
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      keranjang.push({ id, name, price, quantity: 1 });
    }

    localStorage.setItem("keranjang", JSON.stringify(keranjang));
    updateKeranjangDisplay();
  });
});

function updateKeranjangDisplay() {
  const keranjangItems = document.getElementById("keranjangItems");
  const bodyTotalHarga = document.getElementById("bodyTotalHarga");
  const boxButton = document.getElementById("boxButton");
  keranjangItems.innerHTML = "";
  bodyTotalHarga.innerHTML = "";
  boxButton.innerHTML = "";

  let totalHarga = 0;
  let totalItem = 0;

  keranjang.forEach((item) => {
    totalHarga += item.price * item.quantity;
    totalItem += item.quantity;

    const li = document.createElement("li");
    const box = document.createElement("box");
    li.className = "list-group-item d-flex align-items-center justify-content-between";
    li.textContent = `${item.name} - Rp.${(item.price * item.quantity).toFixed(2)} (x${item.quantity})`;

    const increaseButton = document.createElement("button");
    increaseButton.textContent = "+";
    increaseButton.className = "btn btn-success btn-sm";
    increaseButton.onclick = () => increaseItem(item.id);

    const decreaseButton = document.createElement("button");
    decreaseButton.textContent = "-";
    decreaseButton.className = "btn btn-danger btn-sm";
    decreaseButton.onclick = () => decreaseItem(item.id);

    box.classList = "d-flex align-items-center justify-content-end gap-2";
    box.appendChild(increaseButton);
    box.appendChild(decreaseButton);
    li.appendChild(box);
    keranjangItems.appendChild(li);
  });

  if (totalItem > 0) {
    const strong = document.createElement("strong");
    strong.textContent = `Total Price : Rp.${totalHarga.toFixed(2)}`;
    bodyTotalHarga.appendChild(strong);

    const removeAllButton = document.createElement("button");
    removeAllButton.textContent = "Remove All";
    removeAllButton.className = "btn btn-danger mt-3 w-50";
    removeAllButton.onclick = removeAllItems;
    boxButton.appendChild(removeAllButton);

    const checkoutButton = document.createElement("button");
    checkoutButton.textContent = "Checkout";
    checkoutButton.className = "btn btn-primary mt-3 w-50";
    checkoutButton.onclick = () => {
      fetch("../includes/checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(keranjang),
      })
        .then((res) => res.text()) // ubah sementara dari .json() ke .text()
        .then((text) => {
          console.log("Raw response:", text);
          try {
            const data = JSON.parse(text);
            if (data.status === "success") {
              localStorage.removeItem("keranjang");
              window.location.href = `payment.php?order_id=${data.order_id}`;
            } else {
              alert(data.message);
            }
          } catch (err) {
            console.error("Gagal parse JSON:", err);
          }
        })
        .catch((err) => {
          console.error("Fetch error:", err);
        });
    };
    boxButton.appendChild(checkoutButton);
  } else {
    const h1 = document.createElement("h1");
    h1.className = "fs-5 fw-normal text-center";
    h1.textContent = `Shopping cart is empty please select product first`;
    keranjangItems.appendChild(h1);
  }

  document.getElementById("keranjangCountOne").textContent = totalItem;
  document.getElementById("keranjangCountTwo").textContent = totalItem;
}

function increaseItem(id) {
  const item = keranjang.find((item) => item.id === id);
  if (item) {
    item.quantity += 1;
    localStorage.setItem("keranjang", JSON.stringify(keranjang));
    updateKeranjangDisplay();
  }
}

function decreaseItem(id) {
  const itemIndex = keranjang.findIndex((item) => item.id === id);
  if (itemIndex > -1) {
    if (keranjang[itemIndex].quantity > 1) {
      keranjang[itemIndex].quantity -= 1;
    } else {
      keranjang.splice(itemIndex, 1);
    }
    localStorage.setItem("keranjang", JSON.stringify(keranjang));
    updateKeranjangDisplay();
  }
}

function removeAllItems() {
  keranjang = [];
  localStorage.removeItem("keranjang");
  updateKeranjangDisplay();
}

// Toast
document.addEventListener("DOMContentLoaded", function () {
  const toastMsg = sessionStorage.getItem("toastMessage");
  if (toastMsg) {
    document.getElementById("toastMessage").innerText = toastMsg;
    new bootstrap.Toast(document.getElementById("liveToast")).show();
    sessionStorage.removeItem("toastMessage");
  }

  const toastDeleteMsg = sessionStorage.getItem("toastMessageDelete");
  if (toastDeleteMsg) {
    document.getElementById("toastMessageDelete").innerText = toastDeleteMsg;
    new bootstrap.Toast(document.getElementById("liveToastDelete")).show();
    sessionStorage.removeItem("toastMessageDelete");
  }
});
