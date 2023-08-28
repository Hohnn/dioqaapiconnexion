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
      if (res.bookings.length) {
        showBooking();
      } else {
        hideBooking();
      }
    })
    .catch((er) => console.log(er));
}

ajaxCheckBooking();

let timerSpan = document.createElement("span");
const cartBlock = document.querySelector(".blockcart.cart-preview");

function showBooking() {
  /* const menuBlock = document.querySelector(
    ".ets_mm_megamenu_content .ets_mm_megamenu_content_content"
  );

  cartBlock.classList.add("booked");

  let icon = document.createElement("i");
  icon.className = "material-icons";
  icon.innerText = "timer";
  cartBlock.append(icon);

  let icon2 = document.createElement("i");
  icon2.className = "material-icons";
  icon2.innerText = "timer";

  timerSpan.className = "bookingTime";
  let div = document.createElement("div");
  div.className = "bookingTimeContainer";
  div.append(icon2);
  div.append(timerSpan);
  menuBlock.append(div);*/

  const date = document
    .querySelector(".bookingTimeContainer [data-date]")
    .dataset("date");

  countdown(date);
}

function hideBooking() {
  timerSpan.parentNode.remove();
  cartBlock.classList.remove("booked");
}

function countdown(date) {
  const currentDate = new Date(date);
  currentDate.setHours(currentDate.getHours() + 15);

  const expiredDate = currentDate.getTime();
  let timeRemaining = expiredDate - new Date().getTime();

  const interval = 1000;
  // Mettre à jour le compte à rebours immédiatement
  const updateCountdown = () => {
    if (timeRemaining <= 0) {
      clearInterval(countdownInterval);
      console.log("Compte à rebours terminé !");
      /* TODO : afficher la modale pour rajouter du temps */
    } else {
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
    }
  };

  // Appeler la mise à jour initiale puis démarrer l'intervalle
  const countdownInterval = setInterval(updateCountdown, interval);
  updateCountdown();
}

function displayTime(days, hours, minutes, seconds) {
  timerSpan.innerText = `${hours.toString().padStart(2, "0")}:${minutes
    .toString()
    .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
}

prestashop.on("updateCart", ajaxCheckBooking);
