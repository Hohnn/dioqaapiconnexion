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
        showBooking(res.bookings[0].date_add);
      } else {
        hideBooking();
      }
    })
    .catch((er) => console.log(er));
}

ajaxCheckBooking();

let timerSpan = document.querySelector(".bookingTimeContainer .bookingTime");
if (!timerSpan) {
  timerSpan = document.createElement("span");
}
const cartBlock = document.querySelector(".blockcart.cart-preview");

function showBooking(date) {
  if (timerSpan.className === "") {
    const menuBlock = document.querySelector(
      ".ets_mm_megamenu_content .ets_mm_megamenu_content_content"
    );

    let icon2 = document.createElement("i");
    icon2.className = "material-icons";
    icon2.innerText = "timer";

    timerSpan.className = "bookingTime";
    let div = document.createElement("div");
    div.className = "bookingTimeContainer";
    div.append(icon2);
    div.append(timerSpan);
    menuBlock.append(div);
  } else {
    date = document.querySelector(".bookingTimeContainer [data-date]")?.dataset
      ?.date;
  }

  cartBlock.classList.add("booked");
  let icon = document.createElement("i");
  icon.className = "material-icons";
  icon.innerText = "timer";
  cartBlock.append(icon);

  countdown(date);
}

function hideBooking() {
  timerSpan?.parentNode?.remove();
  cartBlock.classList.remove("booked");
}

function countdown(date) {
  const currentDate = new Date(date);
  currentDate.setMinutes(currentDate.getMinutes() - 1);

  const expiredDate = currentDate.getTime();
  let timeRemaining = expiredDate - new Date().getTime();

  let count = 0;

  const interval = 1000;
  // Mettre à jour le compte à rebours immédiatement
  const updateCountdown = () => {
    if (timeRemaining <= 0) {
      clearInterval(countdownInterval);
      console.log("Compte à rebours terminé !");
      showModalBookingExpire();
    } else {
      if (timeRemaining <= 1000) {
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

      timeRemaining -= interval;
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
    el.innerText = `${hours.toString().padStart(2, "0")}:${minutes
      .toString()
      .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
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
  if (countModalBookingOrderNow == 0) {
    $("#bookingModalTimeOut").modal("show");
  }
  countModalBookingOrderNow++;
}

prestashop.on("updateCart", ajaxCheckBooking);
