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
                    <h3 class="text-white">All Players of <span class="text-warning">({{ Str::upper($selectedUser[0]->user_name) }})</span></h3>
                    <p class="text-subtitle text-muted">Players information list</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-last">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" onclick="history.back()">Agents</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Players</li>
                        </ol>
                    </nav>
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
                                <th>Role</th>
                                <th>Username</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Status</th>
                                <th>Commission %</th>
                                <th>Current Points</th>
                                <th>Commission Points</th>
                                <th class="text-center">Modify</th>
                            </tr>    
                        </thead>
                        <tbody id="event-crud">
                            @foreach ($players as $player)
                                <tr id="event_id_{{ $player->id }}">
                                    <td class="name">{{ $player->role_type }}</td>
                                    <td class="name">{{ $player->user_name }}</td>
                                    <td class="name">{{ $player->last_name }}</td>
                                    <td class="name">{{ $player->first_name }}</td>
                                    <td class="name">{{ $player->status }}</td>
                                    @if  ($player->role_type == 'Master_Agent')
                                        <td class="name bg-warning text-white font-bold">Master ({{ $player->commission_percent }})</td>
                                    @elseif  ($player->role_type == 'Gold_Agent')
                                        <td class="name font-bold">Agent ({{ $player->commission_percent }})</td>
                                    @else
                                    <td class="name">With Bet (0.00)</td>
                                    @endif
                                    <td class="name">{{ $player->current_balance }}</td>
                                    <td class="name">{{ $player->current_commission }}</td>
                                    @if (Auth::user()->role_type == 'Operator')
                                        @if ($player->role_type == 'Player')
                                        <td class="text-center">
                                            <a href="javascript:void(0)" id="edit-player" data-id="{{ $player->id }}">
                                                <span class="badge bg-info p-2">EDIT</span>
                                            </a>
                                            <a href="{{ url('player/history/'.$player->id) }}">
                                                <span class="badge bg-warning p-2">HISTORY</span>
                                            </a>
                                            <a href="{{ url('logs/'.$player->id) }}">
                                                <span class="badge bg-light-secondary p-2">LOGS</span>
                                            </a>
                                        </td>
                                        @elseif ($player->role_type == 'Declarator' || $player->role_type == 'Loader')
                                        <td class="text-center">
                                            <a href="javascript:void(0)" id="edit-player" data-id="{{ $player->id }}">
                                                <span class="badge bg-info p-2">EDIT</span>
                                            </a>
                                            <a href="{{ url('history/'.$player->id) }}">
                                                <span class="badge bg-warning p-2">HISTORY</span>
                                            </a>
                                            <a href="{{ url('logs/'.$player->id) }}">
                                                <span class="badge bg-light-secondary p-2">LOGS</span>
                                            </a>
                                        </td>
                                        @else
                                        <td class="text-center">
                                            <a href="javascript:void(0)" id="edit-player" data-id="{{ $player->id }}">
                                                <span class="badge bg-info p-2">EDIT</span>
                                            </a>
                                            <a href="{{ url('history/'.$player->id) }}">
                                                <span class="badge bg-warning p-2">HISTORY</span>
                                            </a>
                                            <a href="{{ url('commission/'.$player->id) }}">
                                                <span class="badge bg-primary p-2">COMM</span>
                                            </a>
                                            <a href="{{ url('admin/players/'.$player->player_code) }}">
                                                <span class="badge bg-success p-2">PLAYERS</span>
                                            </a>
                                            <a href="{{ url('logs/'.$player->id) }}">
                                                <span class="badge bg-light-secondary p-2">LOGS</span>
                                            </a>
                                        </td>
                                        @endif
                                    
                                    @else
                                    @if ($player->role_type == 'Player')
                                        <td class="text-center">
                                            <a href="{{ url('player/history/'.$player->id) }}">
                                                <span class="badge bg-warning p-2">HISTORY</span>
                                            </a>
                                            <a href="{{ url('logs/'.$player->id) }}">
                                                <span class="badge bg-light-secondary p-2">LOGS</span>
                                            </a>
                                        </td>
                                        @elseif ($player->role_type == 'Declarator' || $player->role_type == 'Loader')
                                        <td class="text-center">
                                            <a href="{{ url('history/'.$player->id) }}">
                                                <span class="badge bg-warning p-2">HISTORY</span>
                                            </a>
                                            <a href="{{ url('logs/'.$player->id) }}">
                                                <span class="badge bg-light-secondary p-2">LOGS</span>
                                            </a>
                                        </td>
                                        @else
                                        <td class="text-center">
                                            <a href="{{ url('history/'.$player->id) }}">
                                                <span class="badge bg-warning p-2">HISTORY</span>
                                            </a>
                                            <a href="{{ url('commission/'.$player->id) }}">
                                                <span class="badge bg-primary p-2">COMM</span>
                                            </a>
                                            <a href="{{ url('admin/players/'.$player->player_code) }}">
                                                <span class="badge bg-success p-2">PLAYERS</span>
                                            </a>
                                            <a href="{{ url('logs/'.$player->id) }}">
                                                <span class="badge bg-light-secondary p-2">LOGS</span>
                                            </a>
                                        </td>
                                        @endif
                                    @endif
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
                    <label for="user_name" class="col-sm-2 control-label">Username</label>
                    <div class="col-sm-12">
                        <input disabled="disabled" class="form-control" id="user_name" name="user_name" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="agentCode" class="col-sm-2 control-label">Registered Code</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="agentCode" name="agentCode" value="">
                    </div>
                </div>

                @error('agentCode')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="form-group">
                    <label for="player_role" class="col-sm-4 control-label">Player Role</label>
                    <select class="form-control" id="player_role" name="player_role" value="">
                        <option value="Declarator">Declarator</option>
                        <option value="Loader">Loader</option>
                        <option value="Sub_Operator">Sub Operator</option>
                        <option value="Master_Agent">Master Agent</option>
                        <option value="Gold_Agent">Gold Agent</option>
                        <option value="Player">Player</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="curr_commission" class="col-sm-2 control-label">Set Commission %</label>
                    <div class="col-sm-12">
                        <input type="number" class="form-control" id="curr_commission" name="curr_commission" value="" required="">
                    </div>
                </div>

                <div class="form-group">
                    <label for="player_status" class="col-sm-4 control-label">Player Status</label>
                    <select class="form-control" id="player_status" name="player_status" value="">
                        <option value="Active">Active</option>
                        <option value="Disabled">Disabled</option>
                        <option value="Banned">BANNED</option>

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
            url: "{{ route('getPlayerInfo') }}",
            type: "GET",
            data: {playerId:playerId},
            dataType: 'json',
            success: function (response) {
                console.log(response);
                $('#fightModal').html("Edit Player");
                $('#ajax-crud-modal').modal('show');
                $('#playerId').val(response[0][0].id);
                $('#user_name').val(response[0][0].user_name);
                $('#player_role').val(response[0][0].role_type);  
                $('#curr_commission').val(response[0][0].commission_percent);  
                $('#player_status').val(response[0][0].status);  
                $('#agentCode').val(response[0][0].agent_code);  
            },
            error: function (response) {
                console.log('Error:', response);
            }
        });

     });

    });
   
    if ($("#playerForm").length > 0) {
        $("#playerForm").validate({
   
       submitHandler: function(form) {
        $('#btn-save').html('Saving...');
        var playerId = document.getElementById("playerId").value;
        var playerRole = document.getElementById("player_role").value;
        var currComm = document.getElementById("curr_commission").value;
        var playerStatus = document.getElementById("player_status").value;
        var agentCode = document.getElementById("agentCode").value;
        
        $.ajax({
            url: "{{ route('updatePlayer') }}",
            type: "GET",
            data: {playerId:playerId,playerRole:playerRole,currComm:currComm,playerStatus:playerStatus,agentCode:agentCode},
            success: function (response) {
                location.reload();
            },
            error: function (response) {
                $('#btn-save').html('Save');
                alert('Code not exist!');
                console.log('Error:', response);
            }
        });
      }
    })
  }
     
  </script>
@endpush
@endsection
