<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Legacy17</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{ HTML::Style("css/materialize.min.css") }}
        {{ HTML::Style("css/font-awesome.min.css") }}  
        {{ HTML::Style("css/app.css") }}   
        {{ HTML::Script("js/jquery.min.js") }}        
        {{ HTML::Script("js/materialize.min.js") }} 
        {{ HTML::Script("js/app.js") }}         
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">            
        <style>
            .slider-fixed-item{
                position: relative;
                z-index: 9999;
                bottom: 150px;
                height: 0px;
            }
            @media only screen and (max-width: 640px){
                h1{
                    font-size: 30px;
                }
                h3,h4{
                    font-size: 15px;
                }
                .small-font-awesome{
                    font-size: 30px;
                }
            }
        </style>
    </head>
    <body> 
        @yield('content')        
    </body>
    <script>
        $('.slider').slider({interval: 3000, transition: 1000});
    </script>
</html>