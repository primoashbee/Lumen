<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            /** Define the margins of your page **/
            body{
                
                /* font-family: 'DejaVu', sans-serif; */
                font-family: DejaVu Sans !important;
                margin: 0;
                padding: 0;
            }
            .client-details-wrapper{
                position: absolute;
                top: 50%;
                width: 100%;
            }
            .text-primary{
                font-weight: 800;
                font-size: 18px;
                text-align: center;
            }
            .image-wrapper{
                width: 100%;
                height: 424px;
                border: 1px solid;
            }
            .position-relative{
                position: relative;
                width: 290px;
                height: 442px;
            }
            .client-footer{
                position: absolute;
                right: 10px;
                bottom: 20px;
            }
            .text-sub{
                font-size: 15px;
                text-align: left;
                line-height: 1;
                margin-left: 90px;
                margin-top: -7px;
            }
            .profile_picture-wrapper{
                width: 145px;
                height: 135px;
                border: 1px solid;
                top: 21%;
                left: 25%;

            }
            .w-100{
                width: 100%;
                height: 100%;
            }
            .position-absolute{
                position:absolute;
            }
            .back-text-sub{
                margin-top: 40px;
                font-size: 12px;
                font-weight: 800;
            }

            .back-client-details-wrapper{
                position: absolute;
                top: 17%;
                width: 100%;
                left: 5px;;
                text-align: center;
            }
            .back-client-details-wrapper.sub{
                top: 57%;
                margin-left: 0;
            }
            .card-container{
                display: inline-block;
                margin:0 10px; 
            }
        </style>
        
    </head>
    <body>
        <!-- Define header and footer blocks before your content -->
 

        <div class="main">
            @foreach($clients as $client)
                
                <div class="card-container">
                    <div class="client-container position-relative">
                        <img src="{{public_path('ID-FRONT.png')}}" class="image-wrapper" alt=""> 
                        <div class="profile_picture-wrapper position-absolute">
                            <img src="{{asset($client->profile_picture_path)}}" alt="" class="w-100">
                        </div>
                        <div class="client-details-wrapper">
                            <p class="text-primary">{{$client->firstname. ' '. $client->middlename. ' '. $client->lastname}}</p>
                            <p class="text-sub">{{$client->contact_number}}</p>
                            <p class="text-sub" style="width: 60%;">{{$client->address()}}</p>
                        </div>
                        <div class="client-footer">
                            {{$client->office->name}}
                        </div>
                    </div>
                    <div class="client-container position-relative">
                        <img src="{{public_path('ID-BACK.png')}}" class="image-wrapper" alt=""> 
                        
                        <div class="back-client-details-wrapper">
                            <p class="back-text-sub">{{$client->branch_manager()->fullname}}</p>
                        </div>
                        
                        <div class="back-client-details-wrapper sub">
                            <p class="back-text-sub">{{$client->firstname. ' '. $client->middlename. ' '. $client->lastname}}</p>
                        </div>
                    </div>
                </div>
            @endforeach
            
        </div>
    
       
        


        <!-- Wrap the content of your PDF inside a main tag -->
       
    </body>
</html>