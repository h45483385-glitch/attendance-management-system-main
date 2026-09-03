<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Attendance Management System</title>
        <meta content="Admin Dashboard" name="description" />
        <meta content="Themesbrand" name="author" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        @include('layouts.head')

        <style>
            :root {
                --primary-blue: #116fb7;
                --primary-green: #22C55E;
                --bg-white: #FFFFFF;
                --text-heading: #0F172A;
                --text-normal: #334155;
                --text-secondary: #64748B;
                --brand-gradient: linear-gradient(135deg, #116fb7, #22C55E);
                --glass-bg: rgba(255,255,255,0.85);
                --shadow-light: 0 10px 30px rgba(0,0,0,0.06);
                --shadow-medium: 0 15px 40px rgba(15,23,42,0.06);
            }
        </style>
    </head>
    <body data-sidebar="light"> 
        <div id="wrapper">
             @include('layouts.header')
             @include('layouts.sidebar')
             <div class="content-page">  
                <div class="content">
                    <div class="container-fluid">
                       @include('layouts.settings')
                       @yield('content')
                    </div> 
                </div> 
            </div> 
            @include('layouts.footer')  
            @include('layouts.footer-script')  
        </div> 
        @include('includes.flash')
    </body>
</html>