function listenForModal() {
  const timeOut = document.querySelector(".bookingTimeContainer .bookingTime")
    ?.dataset?.timeOut;

  if (timeOut == "1") {
    $("#bookingModal").modal("show");
  } else {
    $("#bookingModal").modal("hide");
  }
}
listenForModal();
