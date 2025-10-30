/* view-transition.js
if ("startViewTransition" in document) {
    document.addEventListener("click", (e) => {
        const link = e.target.closest("a[href]");
        if (!link || link.target === "_blank" || link.hasAttribute("download")) return;

        e.preventDefault();

        document.startViewTransition(() => {
            return fetch(link.href, { headers: { "X-Requested-With": "fetch" } })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");

                    // Reemplaza el contenido principal
                    document.querySelector("main").innerHTML = doc.querySelector("main").innerHTML;

                    // Cambia la URL sin recargar
                    history.pushState(null, "", link.href);
                });
        });
    });
}
*/