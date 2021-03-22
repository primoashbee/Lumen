

<html>
<head>
  <style>
      /*@import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');*/
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
        font-size: .5em;
      }
      table.table thead tr th{
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #4CAF50;
        color: white;
      }
      table.table tbody tr td{
        border: 1px solid #ddd;
        padding: 8px;
        font-size: .7em;
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
      ul.item_list li{
        display: inline-block;
        width: 33%%;
      }
      .d-inline-block{
        display: inline-block;
      }
      .title{
        width: 89%;
        vertical-align: top;
        margin-top: 20px;
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
  </style>
  <title> Test </title>
</head>
  <body>
    <div class="container"> 
          <div>
            <img src="{{public_path('logo.png')}}" style="width: 10%" alt="">
            <h1 style="margin: 0;" class="d-inline-block title text-center">Collection Sheet</h1>
          </div>
          
          <div class="cs_info">
              <ul class="item_list">
                  <li class="text-left">Office Level : {{$summary->office}}</li>
                  <li class="text-center">Printed By  : {{auth()->user()->full_name }}</li>
                  <li class="text-right">Collection Date: {{$summary->repayment_date}}</li>
              </ul>
          </div>
          <table class="table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Client ID</th>
                    <th>Name</th>
                    @if($summary->has_loan)
                    <th>Loan</th>
                    <th>Interest</th>
                    <th>Principal</th>
                    <th>Amount Due</th>
                    <th>Payment</th>
                    @endif
                    @if($summary->has_deposit)
                    @foreach($summary->deposit_types as $type)
                    <th>{{$type}} - Bal.</th>
                    <th>{{$type}}
                    @endforeach
                    @endif
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
                      <td>{{$item->repayment_info->_interest}}</td>
                      <td>{{$item->repayment_info->_principal}}</td>
                      <td>{{$item->repayment_info->_amount_due}}</td>
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
                    <td>{{$summary->total['loan']['_total_interest']}}</td>
                    <td>{{$summary->total['loan']['_total_principal']}}</td>
                    <td>{{$summary->total['loan']['_total_amount_due']}}</td>
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
                  <li>Cluster Leader : ______________________</li>
                  <li>Loan Officer : ______________________</li>
                  
                  <li>Branch Manager : ______________________ </li>
              </ul>
          </div>  
    </div>

  </body>

</html>
