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
                    <h3 class="text-white">User Logs of <span class="text-warning">({{ Str::upper($user->user_name) }})</span></h3>
                    <p class="text-subtitle text-muted">Player logs information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        @if ($user->role_type == 'Player')
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('getAgents') }}">Agents</a></li>
                            <li class="breadcrumb-item"><a href="#" onclick="history.back()">Players</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Logs</li>
                        </ol>
                        @else
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" onclick="history.back()">Agents</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Logs</li>
                        </ol>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                <div class="card-header">
                     @if (Auth::user()->role_type == 'Operator')
                        <a href="javascript:void(0)" id="password" data-id="{{ $user->id }}" data-user="{{ $user->user_name }}">
                            <span class="p-3 badge bg-primary">FORGOT PASSWORD</span>
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($logs as $log)
                                <tr id="fight_id_{{ $log->id }}">
                                    <td class="name">{{ $log->description }}</td>
                                    <td class="name">{{ ($log->date_time) }}</td>
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
            <form id="playerForm" name="playerForm" class="form-horizontal">
               <input type="hidden" name="playerId" id="playerId">
                <div class="form-group">
                    <label for="username" class="col-sm-2 control-label">USERNAME</label>
                    <div class="col-sm-12">
                        <input disabled="disabled" class="form-control" id="username" name="username" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="new-password" class="col-sm-2 control-label">NEW PASSWORD</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="new-password" name="new-password" value="" required="">
                    </div>
                </div>

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn btn-primary" id="btn-save">Save
                 </button>
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
</script>

<script>
    $(document).ready(function () {
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      
      $('body').on('click', '#password', function () {
        $('#fightModal').html("FORGET PASSWORD");
        $('#ajax-crud-modal').modal('show');
        $('#playerId').val($(this).data('id'));
        $('#username').val($(this).data('user'));
     });

    });
   
    if ($("#playerForm").length > 0) {
        $("#playerForm").validate({
   
       submitHandler: function(form) {
        $('#btn-save').html('Saving...');

        var playerId = document.getElementById("playerId").value;
        var password = document.getElementById("new-password").value;

        $.ajax({
            url: "{{ route('forgetPassword') }}",
            type: "GET",
            data: {playerId:playerId,password:password},
            success: function (response) {
                $('#btn-save').html('Save');
                $('#ajax-crud-modal').modal('hide');
                $('#playerForm').trigger("reset");
                alert('Password Changed');
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });
      }
    })
  }
     
    
</script>
@endpush
@endsection
