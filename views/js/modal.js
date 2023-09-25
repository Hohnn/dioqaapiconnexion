function listenForModal() {
  const timeOut = document.querySelector(".bookingTimeContainer .bookingTime")
    ?.dataset?.time;

  if (timeOut == false) {
    $("#bookingModal").modal("show");
  }
}
listenForModal();
