/* DIOQAAPICONNEXION */

function ajaxCheckBooking() {
  console.log("ajaxCheckBooking");
  var form_data = new FormData();
  form_data.append("action", "checkBooking");

  var baseUrl = "/modules/dioqaapiconnexion/ajax/booking.php";
  fetch(baseUrl, {
    method: "post",
    body: form_data,
    cache: "no-cache",
    contentType: false,
    processData: false,
  })
    .then((res) => res.json())
    .then((res) => {
      console.log(res);
      if (res && res.bookings.length) {
        showBooking(res.bookings[0].date_expire);
      } else {
        hideBooking();
      }
    })
    .catch((er) => console.log(er));
}

ajaxCheckBooking();

let timerSpan = document.querySelector(".bookingTimeContainer .bookingTime");

const cartBlock = document.querySelector(".blockcart.cart-preview");

function showBooking(date) {
  timerSpan?.parentNode?.classList.remove("d-none");
  timerSpan.dataset.date = date;
  countdown(date);
}

function hideBooking() {
  timerSpan?.parentNode?.classList.add("d-none");
  /* cartBlock.classList.remove("booked"); */
}

let count = 0;

function countdown(date) {
  const interval = 1000;
  // Mettre à jour le compte à rebours immédiatement
  const updateCountdown = () => {
    const currentDate = new Date(date);
    /* currentDate.setMinutes(currentDate.getMinutes() - 1); */

    const expiredDate = currentDate.getTime();
    let timeRemaining = expiredDate - new Date().getTime();

    if (timeRemaining <= 0) {
      clearInterval(countdownInterval);
      console.log("Compte à rebours terminé !");
      showModalBookingExpire();
      hideBooking();
    } else {
      if (timeRemaining <= 10000) {
        console.log("bientot fini");
        showModalBookingOrderNow();
      }
      const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
      const hours = Math.floor(
        (timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
      );
      const minutes = Math.floor(
        (timeRemaining % (1000 * 60 * 60)) / (1000 * 60)
      );
      const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

      displayTime(days, hours, minutes, seconds);

      count++;
    }
  };

  // Appeler la mise à jour initiale puis démarrer l'intervalle
  const countdownInterval = setInterval(updateCountdown, interval);
  updateCountdown();
}

function displayTime(days, hours, minutes, seconds) {
  const allTimer = document.querySelectorAll(
    ".bookingTimeContainer .bookingTime"
  );
  allTimer.forEach((el) => {
    el.innerText = `${minutes.toString().padStart(2, "0")}:${seconds
      .toString()
      .padStart(2, "0")}`;
  });
}

function showModalBookingExpire() {
  if (count > 0) {
    location.reload();
    return;
  }
  $("#bookingModal").modal("show");
}

let countModalBookingOrderNow = 0;

function showModalBookingOrderNow() {
  if (
    countModalBookingOrderNow == 0 &&
    !$("body").is("#cart") &&
    !$("body").is("#order")
  ) {
    $("#bookingModalTimeOut").modal("show");
  }
  countModalBookingOrderNow++;
}

prestashop.on("updateCart", ajaxCheckBooking);

function handleModalExpire() {
  const modal = document.getElementById("bookingModal");
  console.log(modal);
  if (!modal) {
    return;
  }

  const inputsRadio = modal.querySelectorAll(".actions .btn-book");
  console.log(inputsRadio);
  if (!inputsRadio) {
    return;
  }

  let count = 0;

  inputsRadio.forEach((input) => {
    input.addEventListener("change", function () {
      hideBookingProduct(this);
      count++;
      console.log(count);
      if (count >= inputsRadio.length) {
        modal.classList.add("loading");
        modal.querySelector("form").submit();
      }
    });
  });
}
handleModalExpire();

function hideBookingProduct(el) {
  let target = el.closest("li");
  console.log(target);
  if (target) {
    target.classList.add("d-none");
  }
}
