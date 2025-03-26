function justuno_generate_random_token() {
  const element = document.getElementById("ju4_justuno_woocommerce_token");
  element.value =
    Math.random().toString(36).substring(2, 24) +
    Math.random().toString(36).substring(2, 24);
}
