const customPage = localStorage.getItem("t_preference_page");
if (customPage) {
    location.href = "./" + customPage;
}

setTimeout(() => {
    document.querySelector("#notice").style.transform = "translateY(0)";
}, 0);