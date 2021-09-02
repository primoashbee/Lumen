<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
                padding: 1px;
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
            .payment {
                width: 100px;
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
                            <li class="text-center">Printed By  : {{$summary->printed_by}}</li>
                            {{-- <li class="text-center">Printed At: {{$summary->printed_at}}</li> --}}
                            <li class="text-center"></li>
                            <li class="text-right">Collection Date: {{$summary->repayment_date}}</li>
                      </ul>
                  </div>
                  <table class="table">
                        <thead>
                          <tr>
                            <th rowspan="1">#</th>
                            <th rowspan="1">Client ID</th>
                            <th rowspan="1">Name</th>
                            @if($summary->has_loan)
                            <th rowspan="1">Loan</th>
                            <th rowspan="1">Term</th>
                            <th rowspan="1"># of Inst.</th>
                            <th rowspan="1">Balance (P+I)</th>
                            <th rowspan="1">Overdue</th>
                            <th rowspan="1">Inst. Due</th>
                            <th rowspan="1">Total Due</th>
                            <th colspan="1" class="payment">Cash</th>
                            <th colspan="1" class="payment">CTLP</th>
                            @endif
                            @if($summary->has_deposit)
                            @foreach($summary->deposit_types as $type)
                            <th rowspan="1">{{$type['code']}} - Bal.</th>
                            <th rowspan="1">{{$type['code']}}
                            @endforeach
                            @endif
                            <th class="payment">Signature </th>
                          </tr>
                          {{-- <tr>
                              <th>CTLP</th>
                              <th>CASH</th>
                          </tr> --}}
                        </thead>
                        <tbody>
                          <?php $ctr = 1;
                                $total_loan_balance = 0;
                                $total_overdue = 0;
                                $total_installment_due = 0;
                                $total_total_due = 0;
                                $deposit_summary = [];
                                if($summary->has_deposit){
                                    foreach($summary->deposit_types as $deposit){
                                        $item = $deposit;
                                        $item['total'] = 0;
                                        $deposit_summary[] = $item;
                                    }
                                }
                          ?>
                          @foreach ($summary->loan_accounts as $item)
                            <tr>
                              <td>{{$ctr}}</td>
                              <td>{{$item->client_id}}</td>
                              <td>{{$item->fullname}}</td>
                              @if($summary->has_loan)
                              <?php 
                                $total_loan_balance +=$item->total_balance; 
                                $total_overdue +=$item->overdue_due; 
                                $total_installment_due +=$item->installment_due; 
                                $total_total_due +=$item->due_due; 
                              ?>
                              <td>{{$item->loan_code}}</td>
                              <td>{{$item->number_of_months}}</td>
                              <td>{{$item->number_of_installments}}</td>
                              <td>{{money($item->total_balance,2)}}</td>
                              <td>{{money($item->overdue_due,2)}}</td>
                              <td>{{money($item->installment_due,2)}}</td>
                              <td>{{money($item->due_due,2)}}</td>
                              <td></td>
                              <td></td>
                              @endif
                              @if($summary->has_deposit)
                                @foreach($summary->deposit_types as $deposit)
                                {{-- <td>{{$item[$deposit->code]}}</td> --}}
                                {{-- <td>{{$item->$deposit['code']}}</td> --}}
                                {{-- <td>{{$deposit['code']}}</td>
                                <td>{{$item->RCBU}}</td> --}}
                                <?php $code = $deposit['code']; ?>
                                <?php 
                                    $index = array_search($deposit['id'], array_column($deposit_summary,'id'));
                                    $deposit_summary[$index]['total'] += $item->$code;
                                ?>
                                <td>{{money($item->$code,2)}}</td>
                                <td></td>
                                @endforeach
                              @endif
                              <td></td>
                            </tr>
                          <?php $ctr++;?>
                          @endforeach
                          <tr>
                            <td></td>
                            <td style="text-align: right"><b># of Accounts</b></td>
                            <td style="text-align: left"><b><?=$ctr?></b></td>
                            <td></td>
                            @if($summary->has_loan)
                            <td></td>
                            <td></td>
                            <td><b>{{money($total_loan_balance,2)}}</b></td>
                            <td><b>{{money($total_overdue,2)}}</b></td>
                            <td><b>{{money($total_installment_due,2)}}</b></td>
                            <td><b>{{money($total_total_due,2)}}</b></td>
                            <td></td>
                            <td></td>
                            @endif
                            @if($summary->has_deposit)
                            @foreach($deposit_summary as $dep)
                                <td><b>{{money($dep['total'],2)}}</b></td>
                                <td class="payment"></td>
                            @endforeach
                            @endif
                            <td></td>
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