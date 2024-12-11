<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago con PayPal</title>
    <!-- Usando USD en lugar de UYU para evitar el error -->
    <script src="https://www.paypal.com/sdk/js?client-id=AXYAdeYRQ8VS6mcKzTk1CwBs6FILFuj9vXPi80pcCT_I9olMkonA0J9iajW3kKe8Z-0KTURJY5XKWOKM&currency=USD"></script>
</head>
<body>
    <h2>Compra el premium</h2>
    <div id="paypal-button-container"></div>

    <script>
        paypal.Buttons({
            // Crear la orden de pago
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '5.00',  // Total de la compra (producto + envío)
                            currency_code: 'USD',  // Asegúrate de que coincida con la moneda especificada en el script
                        },
                        description: 'Producto de ejemplo - Compra en línea'  // Descripción del producto
                    }]
                });
            },
            // Aprobar la transacción
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    console.log(details);
                });
            },
            // En caso de cancelar el pago
            onCancel: function(data) {
                alert('El pago ha sido cancelado');
                console.log('Pago cancelado', data);
            },
            // Si hay un error en el proceso
            onError: function(err) {
                alert('Error durante la transacción: ' + err.message);
                console.error('Error durante la transacción:', err);
            }
        }).render('#paypal-button-container');  // Renderiza el botón en el contenedor
    </script>
</body>
</html>
