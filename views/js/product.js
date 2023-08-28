console.log("product js");

const addToCartBtn = document.querySelector(
  '[data-button-action-custom="add-to-cart"]'
);

const reelAddToCartBtn = document.querySelector(
  '[data-button-action="add-to-cart"]'
);

const PRODUCT_ID = document.getElementById("product_page_product_id").value;

addToCartBtn.addEventListener("click", handleAddToCart);

async function handleAddToCart() {
  console.log("handleAddToCart");
  let IsBookingPossible = await ajaxIsBookingPossible();
  if (IsBookingPossible) {
    reelAddToCartBtn.click();
  }
}

async function ajaxIsBookingPossible() {
  console.log("ajaxCheckBooking");
  let isPossible = false;
  var form_data = new FormData();
  form_data.append("action", "ajaxIsBookingPossible");
  form_data.append("id_product", PRODUCT_ID);

  var baseUrl = "/modules/dioqaapiconnexion/ajax/booking.php";
  await fetch(baseUrl, {
    method: "post",
    body: form_data,
    cache: "no-cache",
    contentType: false,
    processData: false,
  })
    .then((res) => res.json())
    .then((res) => {
      isPossible = res.IsBookingPossible;
    })
    .catch((er) => console.log(er));

  return isPossible;
}
