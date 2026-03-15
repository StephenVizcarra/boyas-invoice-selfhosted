<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #2d2d2d; padding: 48px; }

  .header-table { width: 100%; margin-bottom: 44px; }
  .logo-cell { width: 45%; vertical-align: top; }
  .sender-cell { vertical-align: top; text-align: right; }
  .logo-placeholder { width: 110px; height: 50px; border: 1.5px dashed #ccc; text-align: center; padding-top: 16px; color: #bbb; font-size: 10px; letter-spacing: 0.5px; }
  .sender-name { font-size: 15px; font-weight: bold; color: #111; }
  .sender-detail { color: #555; line-height: 1.6; }

  .invoice-label { font-size: 26px; font-weight: bold; letter-spacing: 1px; color: #111; margin-bottom: 6px; }
  .invoice-meta { font-size: 11px; color: #666; margin-bottom: 3px; }
  .divider { border: none; border-top: 1px solid #ddd; margin: 20px 0; }

  .bill-to-heading { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #999; margin-bottom: 8px; }
  .bill-to-name { font-weight: bold; font-size: 13px; }
  .bill-to-detail { color: #555; line-height: 1.7; }

  table.items { width: 100%; border-collapse: collapse; margin-top: 24px; }
  table.items th {
    font-size: 9px; text-transform: uppercase; letter-spacing: 1px;
    color: #888; padding: 0 0 8px 0; border-bottom: 1.5px solid #222; text-align: left;
  }
  table.items th.right, table.items td.right { text-align: right; }
  table.items td { padding: 11px 0; border-bottom: 1px solid #eee; font-size: 12px; }
  table.items tr.total-row td {
    border-bottom: none; border-top: 2px solid #222;
    font-weight: bold; font-size: 13px; padding-top: 14px;
  }

  .notes-section { margin-top: 32px; font-size: 11px; color: #666; line-height: 1.6; }
  .notes-label { font-weight: bold; color: #444; margin-bottom: 4px; }
</style>
</head>
<body>

{{-- Header: logo left, sender right --}}
<table class="header-table">
  <tr>
    <td class="logo-cell">
      @if($logoData)
        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" style="max-height:70px; max-width:200px;">
      @else
        <div class="logo-placeholder">YOUR LOGO</div>
      @endif
    </td>
    <td class="sender-cell">
      <div class="sender-name">{{ $sender['name'] ?? '' }}</div>
      <div class="sender-detail">
        @if(!empty($sender['company'])){{ $sender['company'] }}<br>@endif
        @if(!empty($sender['address'])){{ $sender['address'] }}<br>@endif
        @if(!empty($sender['city_state_zip'])){{ $sender['city_state_zip'] }}<br>@endif
        @if(!empty($sender['email'])){{ $sender['email'] }}<br>@endif
        @if(!empty($sender['phone'])){{ $sender['phone'] }}@endif
      </div>
    </td>
  </tr>
</table>

{{-- Invoice title & meta --}}
<div class="invoice-label">INVOICE</div>
<div class="invoice-meta">Invoice #: <strong>{{ $invoiceNumber }}</strong></div>
<div class="invoice-meta">Date: {{ $date }}</div>

<hr class="divider">

{{-- Bill To --}}
<div class="bill-to-heading">Bill To</div>
<div class="bill-to-name">{{ $recipient['name'] }}</div>
<div class="bill-to-detail">
  @if(!empty($recipient['company'])){{ $recipient['company'] }}<br>@endif
  @if(!empty($recipient['address'])){{ $recipient['address'] }}<br>@endif
  @if(!empty($recipient['city_state_zip'])){{ $recipient['city_state_zip'] }}<br>@endif
  @if(!empty($recipient['email'])){{ $recipient['email'] }}@endif
</div>

{{-- Line items --}}
<table class="items">
  <thead>
    <tr>
      <th>Description</th>
      <th class="right">Amount</th>
    </tr>
  </thead>
  <tbody>
    @foreach($lineItems as $item)
    <tr>
      <td>{{ $item['description'] }}</td>
      <td class="right">${{ number_format((float)$item['amount'], 2) }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
      <td>Total</td>
      <td class="right">${{ number_format($total, 2) }}</td>
    </tr>
  </tbody>
</table>

@if(!empty($notes))
<div class="notes-section">
  <div class="notes-label">Notes</div>
  {{ $notes }}
</div>
@endif

</body>
</html>
