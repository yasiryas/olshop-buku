document.addEventListener("alpine:init", () => {
    Alpine.data("cartQty", ({ id, quantity, max, updateUrl, token }) => ({
        quantity,
        max,
        isUpdating: false,

        increase() {
            if (this.quantity < this.max) {
                this.quantity++;
                this.updateServer();
            }
        },

        decrease() {
            if (this.quantity > 1) {
                this.quantity--;
                this.updateServer();
            }
        },

        updateServer() {
            if (this.isUpdating) return;
            this.isUpdating = true;

            fetch(updateUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                        "X-HTTP-Method-Override": "PUT"
                    },
                    body: JSON.stringify({
                        quantity: this.quantity
                    })
                })
                .then(res => res.json())
                .then(data => {
                    console.log("UPDATED SERVER:", data);
                    calculatePrice(); // refresh total kalau kamu pakai fungsi ini
                })
                .catch(err => console.error("ERROR:", err))
                .finally(() => {
                    this.isUpdating = false;
                });
        }
    }));
});