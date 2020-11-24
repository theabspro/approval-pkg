app.component('approvalFlowConfigurationList', {
    templateUrl: approval_flow_configuration_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
        $scope.loading = true;
        $('#search_approval_flow_configuration').focus();
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('flow-configurations')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.add_permission = self.hasPermission('add-flow-configuration');
        $http.get(
            laravel_routes['getApprovalFlowConfigurationFilter']
        ).then(function(response) {
            self.category_list = response.data.category_list;
        });
        var table_scroll;
        var dataTable;
        setTimeout(function() {
            table_scroll = $('.page-main-content.list-page-content').height() - 37;
            dataTable = $('#approval_flow_configuration_list').DataTable({
                "dom": cndn_dom_structure,
                "language": {
                    // "search": "",
                    // "searchPlaceholder": "Search",
                    "lengthMenu": "Rows _MENU_",
                    "paginate": {
                        "next": '<i class="icon ion-ios-arrow-forward"></i>',
                        "previous": '<i class="icon ion-ios-arrow-back"></i>'
                    },
                },
                pageLength: 10,
                processing: true,
                stateSaveCallback: function(settings, data) {
                    localStorage.setItem('CDataTables_' + settings.sInstance, JSON.stringify(data));
                },
                stateLoadCallback: function(settings) {
                    var state_save_val = JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                    if (state_save_val) {
                        $('#search_approval_flow_configuration').val(state_save_val.search.search);
                    }
                    return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                },
                serverSide: true,
                paging: true,
                stateSave: true,
                ordering: true,
                scrollY: table_scroll + "px",
                scrollCollapse: true,
                ajax: {
                    url: laravel_routes['getApprovalFlowConfigurationList'],
                    type: "GET",
                    dataType: "json",
                    data: function(d) {
                        // d.approval_flow_configuration_name = $('#approval_flow_configuration_name').val();
                        // d.category = $('#category_id').val();
                        // d.status = $('#status').val();
                    },
                },

                columns: [
                    { data: 'action', class: 'action', name: 'action', searchable: false },
                    { data: 'approval_type', name: 'configs.name', searchable: true },
                    { data: 'approval_level', name: 'approval_levels.name', searchable: true },
                    { data: 'next_status', name: 'ns.name', searchable: true },
                    { data: 'value', name: 'approval_flow_configurations.value', searchable: true },
                ],
                "initComplete": function(settings, json) {
                    $('.dataTables_length select').select2();
                },
                "infoCallback": function(settings, start, end, max, total, pre) {
                    $('#table_info').html(total)
                    $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
                },
                rowCallback: function(row, data) {
                    $(row).addClass('highlight-row');
                }
            });
        }, 1000);
        // $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_approval_flow_configuration').val('');
            $('#approval_flow_configuration_list').DataTable().search('').draw();
        }

        $('.refresh_table').on("click", function() {
            $('#approval_flow_configuration_list').DataTable().ajax.reload();
        });
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });
        // var dataTables = $('#approval_flow_configuration_list').dataTable();
        $("#search_approval_flow_configuration").keyup(function() {
            // dataTables.fnFilter(this.value);
            dataTable
                 .search(this.value)
                 .draw();
        });

        //DELETE
        $scope.deleteApprovalFlowConfiguration = function($id) {
            $('#approval_flow_configuration_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#approval_flow_configuration_id').val();
            $http.get(
                laravel_routes['deleteApprovalFlowConfiguration'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Verification Flow Configuration Deleted Successfully');
                    $('#approval_flow_configuration_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/approval-pkg/approval-flow-configuration/list');
                }
            });
        }

        //FOR FILTER
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];

        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        $scope.clearSearchTerm = function() {
            $scope.searchEntity = '';
            $scope.searchStatus = '';
        };
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        $('#approval_flow_configuration_name').on('keyup', function() {
            // dataTables.fnFilter();
            dataTable.draw();
        });
        $scope.onSelectedCategory = function(category_id) {
            $("#category_id").val(category_id);
            // dataTables.fnFilter();
            dataTable.draw();
        }
        $scope.onSelectedStatus = function(status_id) {
            $("#status").val(status_id);
            // dataTables.fnFilter();
            dataTable.draw();
        }
        $scope.reset_filter = function() {
            $("#approval_flow_configuration_name").val('');
            $("#category_id").val('');
            $("#status").val('');
            // dataTables.fnFilter();
            dataTable.draw();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('approvalFlowConfigurationForm', {
    templateUrl: approval_flow_configuration_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('add-flow-configuration') || !self.hasPermission('edit-flow-configuration')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getApprovalFlowConfigurationFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            console.log(response);
            self.approval_flow_configuration = response.data.approval_flow_configuration;
            self.extras = response.data.extras;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.approval_flow_configuration.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        $scope.clearSearchTerm = function() {
            $scope.searchTerm = '';
        };

        $("input:text:visible:first").focus();

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'approval_level_id': {
                    required: true,
                },
                'value': {
                    required: true,
                },
                'next_status_id': {
                    required: true,
                },
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveApprovalFlowConfiguration'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/approval-pkg/approval-flow-configuration/list')
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/approval-pkg/approval-flow-configuration/list')
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});