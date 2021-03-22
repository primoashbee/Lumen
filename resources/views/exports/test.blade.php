<html>
    <head>
        <style>
            /** Define the margins of your page **/
        

            header {
                position: relative;
                top: 0px;
                left: 0px;
                right: 0px;
                height: 50px;

                /** Extra personal styles **/
                
                color: black;
                text-align: center;
                line-height: 35px;
            }

            footer {
                position: fixed; 
                bottom: 0; 
                left: 0px; 
                right: 0px;

                height: 50px; 

                /** Extra personal styles **/
                /* background-color: #03a9f4; */
                color: black;
                text-align: center;
                line-height: 35px;
                font-size: 12px;
            }

            body{
                /* font-family: 'DejaVu', sans-serif; */
                font-family: DejaVu Sans !important;
                margin: 0;
                padding: 0;
            }
            ul{
                margin: 0;
                padding: 0;
            }
            table.table{
                width: 100%;
            }
            .footer{
                margin-top: 30px;
            }
            input[type="text"].form-control{
                border: 0;
                outline: 0;
                background: transparent;
                border-bottom: 1px solid black;
            }
            table.table{
                border-collapse: collapse;
            }
            table.table thead tr th{
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 1em;
            }
            table.table thead tr th{
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: center;
                background-color: #4CAF50;
                color: white;
                border-color: green;
                color:black;
            }
            table.table tbody tr td{
                border: 1px solid #ddd;
                padding: 8px;
                font-size: .8em;
                white-space: nowrap;
            }
            table tr:nth-child(even){
                background-color: #f2f2f2;
            }
            table tr:hover{
                    background-color: #ddd;
            }
            ul{
                margin:10px 0;
            }
            div.footer ul.item_list li{
                display: inline-block;
                width: 33%;
            }
            .cs_info ul.item_list li{
                display: inline-block;
                width: 24.5%;
            }
            .d-inline-block{
                display: inline-block;
            }
            .title{
                width: 100%;
                vertical-align: top;
                margin-top: 15px;
                font-size: 2.5em;
            }
            .text-center{
                text-align: center;
            }
            .text-center{
                text-align: center;
            }
            .text-right{
                text-align: right;
            }
            .cs_info{
                margin-top: 20px;
            }
        </style>
        <title>{{$summary->office.' - '.$summary->repayment_date}}</title>
    </head>
    <body>
        <!-- Define header and footer blocks before your content -->
        <header>
            <!-- <img src="logo.png" style="width:10%; position:absolute;" alt=""> -->
             <img src="{{public_path('logo.png')}}" style="width:10%;position: absolute;" alt=""> 
            <h1 class="h1 d-inline-block title text-center">Collection Sheet</h1>
        </header>

        <footer>
            {{-- <span id="company" class="d-inline-block" style="text-align:left;width:49.5%">LIGHT Microfinance Inc &copy; <?php echo date("Y");?> </span>
            <span class="d-inline-block" style="text-align:right;width:49.5%"><i>Lumen v1.00</i></span> --}}
        </footer>

        <!-- Wrap the content of your PDF inside a main tag -->
        <main class="ccr-content">
            {{-- <p style="page-break-after: always;"> --}}
                  <div class="cs_info">
                      <ul class="item_list">
                            <li class="text-left">Office Level : {{$summary->office}}</li>
                            <li class="text-center">Printed By  : {{auth()->user()->full_name }}</li>
                            <li class="text-center">Printed At: {{\Carbon\Carbon::now()->format('F j, Y, g:i a')}}</li>
                            <li class="text-right">Collection Date: {{$summary->repayment_date}}</li>
                      </ul>
                  </div>
                  <table class="table">
                        <thead>
                          <tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Client ID</th>
                            <th rowspan="2">Name</th>
                            @if($summary->has_loan)
                            <th rowspan="2">Loan</th>
                            <th rowspan="2">Term</th>
                            <th rowspan="2"># of Inst.</th>
                            <th rowspan="2">Loan Balance (P+I)</th>
                            <th rowspan="2">Overdue</th>
                            <th rowspan="2">Installment Due</th>
                            <th rowspan="2">Total Due</th>
                            <th colspan="2">Payment</th>
                            @endif
                            @if($summary->has_deposit)
                            @foreach($summary->deposit_types as $type)
                            <th rowspan="2">{{$type}} - Bal.</th>
                            <th rowspan="2">{{$type}}
                            @endforeach
                            @endif
                          </tr>
                          <tr>
                              <th>CTLP</th>
                              <th>CASH</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $ctr = 1;?>
                          @foreach ($summary->loan_accounts as $item)
                            <tr>
                              <td>{{$ctr}}</td>
                              <td>{{$item->client->client_id}}</td>
                              <td>{{$item->client->full_name}}</td>
                              @if($summary->has_loan)
                              <td>{{$item->product->code}}</td>
                              <td>{{$item->number_of_months}}</td>
                              <td>{{$item->number_of_installments}} ({{$item->product->installment_method}})</td>
                              <td>{{$item->_total_balance}}</td>
                              <td>{{$item->overdue->_total}}</td>
                              <td>{{$item->due->_total}}</td>
                              <td>{{$item->total_due->formatted_total}}</td>
                              <td></td>
                              <td></td>
                              @endif
                              @if($summary->has_deposit)
                                @foreach($item->client->deposits as $deposit)
                                <td>{{$deposit->balance_formatted}}</td>
                                <td></td>
                                @endforeach
                              @endif
                            </tr>
                          <?php $ctr++;?>
                          @endforeach
                          <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @if($summary->has_loan)
                            <td></td>
                            <td></td>
                            <td>{{$summary->total['loan']['_loan_balance']}}</td>
                            <td>{{$summary->total['loan']['overdue']['_total_amount_due']}}</td>
                            <td>{{$summary->total['loan']['due']['_total_amount_due']}}</td>
                            <td>{{$summary->total['loan']['total_due']['_total_amount_due']}}</td>
                            <td></td>
                            <td></td>
                            @endif
                            @if($summary->has_deposit)
                            @foreach($summary->total['deposits'] as $dep)
                                <td>{{$dep['_total_balance']}}</td>
                                <td></td>
                            @endforeach
                            @endif
                          </tr>
                        </tbody>
                  </table>
                  <div class="footer">
                      <ul class="item_list">
                          <li class="text-left">Cluster Leader : ______________________</li>
                          <li class="text-center">Loan Officer : ______________________</li>
                          
                          <li class="text-right">Branch Manager : ______________________ </li>
                      </ul>
                  </div>  
            {{-- </p> --}}
        </main>
    </body>
</html>