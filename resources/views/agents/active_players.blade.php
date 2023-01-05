@extends('layouts.master')
@push('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
@section('menu')
@extends('sidebar.players')
@endsection
@section('content')
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-first">
                    <h3 class="text-white">All Active Players</h3>
                    <p class="text-subtitle text-muted">Players information list</p>
                </div>
            </div>
        </div>
        {{-- message --}}
        {!! Toastr::message() !!}
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Status</th>
                                <th>Current Points</th>
                                <th class="text-center">Modify</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($agents as $agent)
                                <tr id="event_id_{{ $agent->id }}">
                                    <td class="name">{{ $agent->user_name }}</td>
                                    <td class="name">{{ $agent->last_name }}</td>
                                    <td class="name">{{ $agent->first_name }}</td>
                                    <td class="name">{{ $agent->status }}</td>
                                    <td class="name">@comma($agent->current_balance)</td>
                                    <td class="text-center">
                                        <a href="javascript:void(0)" id="edit-player" data-id="{{ $agent->id }}">
                                            <span class="badge bg-info p-2">EDIT</span>
                                        </a>
                                        <a href="javascript:void(0)" id="deposit" data-id="{{ $agent->id }}" data-user="{{ $agent->user_name }}">
                                            <span class="badge bg-success p-2">CASH IN</span>
                                        </a>
                                        <a href="{{ url('bettinng/history/'.$agent->id) }}">
                                            <span class="badge bg-warning p-2">BETTING</span>
                                        </a>
                                        <a href="javascript:void(0)" id="withdraw" data-id="{{ $agent->id }}" data-user="{{ $agent->user_name }}">
                                            <span class="badge bg-danger p-2">CASH OUT</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- Player MODAL --}}
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
                    <label for="user_name" class="col-sm-2 control-label">Username</label>
                    <div class="col-sm-12">
                        <input disabled="disabled" class="form-control" id="user_name" name="user_name" value="">
                    </div>
                </div>

                @if (Auth::user()->role_type == 'Sub_Operator')
                <div class="form-group">
                    <label for="player_role" class="col-sm-4 control-label">Player Role</label>
                    <select class="form-control" id="player_role" name="player_role" value="">
                        <option value="Player">Player</option>
                        <option value="Master_Agent">Master Agent</option>
                    </select>
                </div>
                @elseif (Auth::user()->role_type == 'Master_Agent')
                <div class="form-group">
                    <label for="player_role" class="col-sm-4 control-label">Player Role</label>
                    <select class="form-control" id="player_role" name="player_role" value="">
                        <option value="Player">Player</option>
                        <option value="Gold_Agent">Gold Agent</option>
                    </select>
                </div>
                @else
                <input type="hidden" name="player_role" id="player_role">
                @endif

                <div class="form-group">
                    <label for="player_status" class="col-sm-4 control-label">Player Status</label>
                    <select class="form-control" id="player_status" name="player_status" value="">
                        <option value="Active">Active</option>
                        <option value="Disabled">Disabled</option>
                    </select>
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

{{-- Cash MODAL --}}
<div class="modal fade w-100" id="ajax-cash-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="cashModal"></h4>
        </div>
        <div class="modal-body">
            <form id="cashForm" name="cashForm" class="form-horizontal">
               <input type="hidden" name="agent-playerId" id="agent-playerId">
               <div class="form-group">
                    <label for="agent_name" class="col-sm-2 control-label">Username</label>
                    <div class="col-sm-12">
                        <input disabled="disabled" class="form-control" id="agent_name" name="agent_name" value="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="amount" class="col-sm-2 control-label">Enter Amount</label>
                    <div class="col-sm-12">
                        <input type="number" class="form-control" id="amount" name="amount" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="note" class="col-sm-2 control-label">Leave a note</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="note" name="note" value="" required="">
                    </div>
                </div>

                

                <div class="col-sm-offset-2 col-sm-10">
                 <button type="submit" class="btn text-white" id="btn-cash-save">
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


      
      $('body').on('click', '#edit-player', function () {
        var playerId = $(this).data('id');

        $.ajax({
            url: "{{ route('getAgengInfo') }}",
            type: "GET",
            data: {playerId:playerId},
            dataType: 'json',
            success: function (response) {
                $('#fightModal').html("Edit Player");
                $('#ajax-crud-modal').modal('show');
                $('#playerId').val(response[0][0].id);
                $('#player_role').val(response[0][0].role_type);
                $('#user_name').val(response[0][0].user_name);
                $('#player_status').val(response[0][0].status);  
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });

     });

     $('body').on('click', '#deposit', function () {
        $('#cashModal').html("CASH IN");
        $('#ajax-cash-modal').modal('show');
        $('#agent-playerId').val($(this).data('id'));
        $('#agent_name').val($(this).data('user'));
        $('#btn-cash-save').val("cashin");
        $('#btn-cash-save').html("Cash In");
        document.getElementById('btn-cash-save').classList.add('bg-success');
        document.getElementById('btn-cash-save').classList.remove('bg-danger');
        document.getElementById('btn-cash-save').classList.remove('bg-warning');
     });

     $('body').on('click', '#withdraw', function () {
        $('#cashModal').html("CASH OUT");
        $('#ajax-cash-modal').modal('show');
        $('#agent-playerId').val($(this).data('id'));
        $('#agent_name').val($(this).data('user'));
        $('#btn-cash-save').val("cashout");
        $('#btn-cash-save').html("Cash Out");
        document.getElementById('btn-cash-save').classList.add('bg-danger');
        document.getElementById('btn-cash-save').classList.remove('bg-success');
        document.getElementById('btn-cash-save').classList.remove('bg-warning');
     });

     $('body').on('click', '#convert', function () {
        $('#cashModal').html("COMMISSION OUT");
        $('#ajax-cash-modal').modal('show');
        $('#agent-playerId').val($(this).data('id'));
        $('#agent_name').val($(this).data('user'));
        $('#btn-cash-save').val("convert");
        $('#btn-cash-save').html("Commission Out");
        document.getElementById('btn-cash-save').classList.add('bg-warning');
        document.getElementById('btn-cash-save').classList.remove('bg-danger');
        document.getElementById('btn-cash-save').classList.remove('bg-success');
     });
     

    });
   
    if ($("#playerForm").length > 0) {
            $("#playerForm").validate({
    
        submitHandler: function(form) {
            $('#btn-save').html('Saving...');
            var playerId = document.getElementById("playerId").value;
            var role = document.getElementById("player_role").value;
            var playerStatus = document.getElementById("player_status").value;
            

            $.ajax({
                url: "{{ route('updateAgent') }}",
                type: "GET",
                data: {playerId:playerId,playerStatus:playerStatus,role:role},
                success: function (response) {
                    location.reload();
                },
                error: function (response) {
                    console.log('Error:', response);
                }
            });
        }
        })
    }

    if ($("#cashForm").length > 0) {
            $("#cashForm").validate({
    
        submitHandler: function(form) {
            var playerId = document.getElementById("agent-playerId").value;
            var amount = document.getElementById("amount").value;
            var note = document.getElementById("note").value;
            var saveValue = document.getElementById("btn-cash-save").value;

            if(saveValue == 'cashin'){
                $('#btn-cash-save').html('Cashing in...');
            }else if(saveValue == 'cashout'){
                $('#btn-cash-save').html('Cashing out...');
            }else{
                $('#btn-cash-save').html('Commission transferring...');
            }

            $.ajax({
            url: "{{ route('agentCash') }}",
            type: "GET",
            data: {playerId:playerId,amount:amount,note:note,saveValue:saveValue},
            success: function (response) {
                if(response.success){
                    location.reload();
                }else{
                    $('#ajax-cash-modal').modal('hide');
                    $('#cashForm').trigger("reset");
                    alert('NOT ENOUGH POINT!');
                }
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
