<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>OKS Oasis</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css">

    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

</head>
<body>
    
    <div id="app">
        
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    {{-- <script src="{{ asset('js/httpclient.js') }}"></script> --}}

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('firebase-messaging-sw.js')
                .then(reg => {
                    console.log(`Service Worker Registration (Scope: ${reg.scope})`);
                })
                .catch(error => {
                    const msg = `Service Worker Error (${error})`;
                    console.error(msg);
                });
        } else {
            // happens when the app isn't served over HTTPS or if the browser doesn't support service workers
            console.warn('Service Worker not available');
        }
    </script>

</body>
</html>