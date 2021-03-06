app.component('entityStatusList', {
    templateUrl: entity_status_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $element, $mdSelect) {
        $scope.loading = true;
        var self = this;
        $('#search_entity_status').focus();
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('entity-statuses')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.add_permission = self.hasPermission('add-entity-status');
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#entity_status_list').DataTable({
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
                    $('#search_entity_status').val(state_save_val.search.search);
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
                url: laravel_routes['getEntityStatusList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.entity_status_name = $('#entity_status_name').val();
                    d.entity_id = $('#entity_id').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'entity_statuses.name' },
                { data: 'entity', name: 'configs.name' },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_entity_status').val('');
            $('#entity_status_list').DataTable().search('').draw();
        }

        $('.refresh_table').on("click", function() {
            $('#entity_status_list').DataTable().ajax.reload();
        });

        var dataTables = $('#entity_status_list').dataTable();
        $("#search_entity_status").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteEntityStatus = function($id) {
            $('#entity_status_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#entity_status_id').val();
            $http.get(
                laravel_routes['deleteEntityStatus'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Entity Status Deleted Successfully');
                    $('#entity_status_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/approval-pkg/entity-status/list');
                }
            });
        }

        //FOR FILTER
        $http.get(
            laravel_routes['getEntityStatusFilter']
        ).then(function(response) {
            self.entity_list = response.data.category_list;
        });

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

        $('#entity_status_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.onSelectedEntity = function(id) {
            $("#entity_id").val(id);
            dataTables.fnFilter();
        }
        $scope.onSelectedStatus = function(id) {
            $("#status").val(id);
            dataTables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#entity_status_name").val('');
            $("#entity_id").val('');
            $("#status").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('entityStatusForm', {
    templateUrl: entity_status_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('add-approval-type') || !self.hasPermission('edit-approval-type')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getEntityStatusFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.entity_status = response.data.entity_status;
            self.entity_list = response.data.entity_list;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.entity_status.deleted_at) {
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
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'entity_id': {
                    required: true,
                },
            },
            messages:{
                'name':{
                    minlength: "Minimum 3 Characters",
                    maxlength: "Maximum 191 Characters",
                }, 
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEntityStatus'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/approval-pkg/entity-status/list');
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
                                $location.path('/approval-pkg/entity-status/list');
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