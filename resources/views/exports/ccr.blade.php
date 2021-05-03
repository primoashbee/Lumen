<!DOCTYPE html>
<html>
<head>
<style>
*{ font-family: DejaVu Sans !important;}

#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 50%;,
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
  font-size: 0.45em;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>
</head>
<body>
<img src="{{public_path('logo.png')}}" alt="" style="max-width:150px;max-height: 100px" />
<table id="customers">
    <tr>
        <th>#</th>
        <th>Client ID</th>
        <th>Name</th>
        <th>Loan</th>
        <th>Interest</th>
        <th>Principal</th>
        <th>Payment</th>
        @if($summary->has_deposit)
            @foreach ($summary->deposit_list as $item)
                <th>{{$item->type->product_id}} - Balance</th>
                <th>{{$item->type->product_id}}</th>
            @endforeach
        @endif
    </tr>
    <?php $ctr = 1; ?>
    @foreach ($summary->data as $value)
    <tr>
        <td> {{$ctr}} </td>
        <td>{{$value->client_id}}</td>
        <td>{{$value->client->full_name}}</td>
        <td>{{$value->product->code}}</td>
        <td>{{$value->repayment_info->_interest}}</td>
        <td>{{$value->repayment_info->_principal}}</td>
        <td>{{$value->repayment_info->_amount_due}}</td>
        @if($summary->has_deposit)
            @foreach ($value->client->deposits as $deposit)
                <td>{{$deposit->balance_formatted}}</td>
                <td><input type="text" class="form-control" /></td>
            @endforeach
        @endif
    </tr>
    <?php $ctr++;?>
    @endforeach
</table>
</div>
</body>
</html>

<html>
<head>
