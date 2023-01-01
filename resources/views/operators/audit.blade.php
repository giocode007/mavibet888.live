@extends('layouts.player')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('content')
<div id="main">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-first">
                    <h3 class="text-white">Audit Trail</h3>
                    <p class="text-subtitle text-muted">transaction information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">History</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                <div class="d-flex card-header justify-content-between">
                    <a href="javascript:void(0)" id="profit">
                        <span class="p-3 badge bg-dark"><i class="icon-mid bi bi-plus-circle me-2"></i>PROFIT</span>
                    </a>
                </div>
                <div id="crud-table" class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Transaction</th>
                                <th>Amount</th>
                                <th>Current Balance</th>
                                <th>Current Commission</th>
                                <th>Note</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Date</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($auditHistory as $transaction)
                            <tr id="transaction_id_{{ $transaction['id'] }}">
                                <td class="name">{{ $transaction['username'] }}</td>
                                <td class="name" style="text-transform:uppercase;">{{ $transaction['transaction_type'] }}</td>
                                @if ($transaction['status'] == 1)
                                    <td class="name bg-success text-white">{{ $transaction['amount'] }}</td>
                                @elseif ($transaction['status'] == 2)
                                    <td class="name bg-danger text-white">{{ $transaction['amount'] }}</td>
                                @endif
                                <td class="name">{{ $transaction['current_balance'] }}</td>
                                <td class="name">{{ $transaction['current_commission'] }}</td>
                                <td class="name">{{ $transaction['note'] }}</td>
                                <td class="name">{{ $transaction['from'] }}</td>
                                <td class="name">{{ $transaction['to'] }}</td>
                                <td class="name">{{ $transaction['approved_date_time'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade w-100" id="ajax-crud-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="fightModal"></h4>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('computeProfit') }}" class="form-horizontal">
                @csrf
                <div class="form-group">
                    <label for="from_date_time" class="col-sm-2 control-label">FROM DATE</label>
                    <div class="col-sm-12">
                        <input type="datetime-local" class="form-control" name="from_date_time" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="to_date_time" class="col-sm-2 control-label">TO DATE</label>
                    <div class="col-sm-12">
                        <input type="datetime-local" class="form-control" name="to_date_time" value="" required="">
                    </div>
                </div>

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn bg-warning text-white" >Compute</button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            
        </div>
    </div>
    </div>
    </div>

@push('scripts')
<script>
    // Simple Datatable
    let table1 = document.querySelector('#table1');
    let dataTable = new simpleDatatables.DataTable(table1);

    $(document).ready(function () {
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      
      $('body').on('click', '#profit', function () {
        $('#fightModal').html("DEPOSIT");
        $('#ajax-crud-modal').modal('show');
     });

    });
   
     
    
  </script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
        config = {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
    }
    flatpickr("input[type=datetime-local]", config);
</script>
@endpush
@endsection
