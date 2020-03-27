app.component('approvalTypeList', {
    templateUrl: approval_type_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect) {
        $scope.loading = true;
        $('#search_approval_type').focus();
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('approval-types')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#approval_types_list').DataTable({
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
                    $('#search_approval_type').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
            },
            serverSide: true,
            paging: true,
            stateSave: true,
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getApprovalTypeList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.approval_type_name = $('#approval_type_name').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', searchable: false },
                { data: 'name', name: 'approval_types.name', searchable: true },
                { data: 'approval_type_code', name: 'approval_types.code', searchable: true },
                { data: 'entity_type', name: 'e.name', searchable: true },
                { data: 'no_of_levels', searchable: false },
                // { data: 'no_of_status', searchable: false },
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
            $('#search_approval_type').val('');
            $('#approval_types_list').DataTable().search('').draw();
        }
        $('.refresh_table').on("click", function() {
            $('#approval_types_list').DataTable().ajax.reload();
        });

        var dataTables = $('#approval_types_list').dataTable();
        $("#search_approval_type").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteApprovalType = function($id) {
            $('#approval_type_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#approval_type_id').val();
            $http.get(
                laravel_routes['deleteApprovalType'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: response.data.message,
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#approval_types_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    $noty = new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: response.data.errors,
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                }
            });
        }

        //FOR FILTER
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];

        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        $('#approval_type_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            dataTables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#approval_type_name").val('');
            $("#status").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('approvalTypeForm', {
    templateUrl: approval_type_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('add-approval-type') || !self.hasPermission('edit-approval-type')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.angular_routes = angular_routes;
        $http({
            url: laravel_routes['getApprovalTypeFormData'],
            method: "GET",
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) {
            console.log(response.data);
            self.approval_type = response.data.approval_type;
            self.entity_list = response.data.entity_list;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.approval_type.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        /*$('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}*/
        $("input:text:visible:first").focus();

        self.addNewApprovalTypeStatus = function() {
            self.approval_type.approval_type_statuses.push({
                id: '',
                status: '',
                switch_value: 'Active',
            });
        }
        self.approval_type_status_removal_ids = [];
        self.removeApprovalTypeStatus = function(index, approval_type_status_id) {
            if (approval_type_status_id) {
                self.approval_type_status_removal_ids.push(approval_type_status_id);
                $('#approval_type_status_removal_ids').val(JSON.stringify(self.approval_type_status_removal_ids));
            }
            self.approval_type.approval_type_statuses.splice(index, 1);
        }

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'code': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'entity_id': {
                    required: true,
                },
            },
            invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 3000)
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveApprovalType'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (!res.success) {
                            $('#submit').prop('disabled', 'disabled');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            $noty = new Noty({
                                type: 'error',
                                layout: 'topRight',
                                text: errors
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $('#submit').button('reset');

                        } else {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Approval Type ' + res.comes_from + ' Successfully',
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $('#submit').button('reset');

                            $location.path('/approval-pkg/approval-type/list')
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 3000);
                    });
            }
        });
    }
});

//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('approvalTypeView', {
    templateUrl: approval_type_view_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('view-approval-type')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.angular_routes = angular_routes;
        $http({
            url: laravel_routes['viewApprovalType'],
            method: "GET",
            params: {
                'id': $routeParams.id,
            }
        }).then(function(response) {
            console.log(response.data);
            self.approval_type = response.data.approval_type;
            self.extras = response.data.extras;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'View') {
                if (self.approval_type.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
                angular.forEach(response.data.approval_type.approval_levels, function(value, key) {
                    //console.log(key, value);
                    if (value.pivot.has_email_noty == 1) {
                        value.has_email_noty = 'Yes';
                    } else {
                        value.has_email_noty = 'No';
                    }
                    if (value.pivot.has_sms_noty == 1) {
                        value.has_sms_noty = 'Yes';
                    } else {
                        value.has_sms_noty = 'No';
                    }
                });
            }
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        /*$('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}*/

        self.addNewApprovalLevel = function() {
            self.approval_type.approval_levels.push({
                id: '',
                name: '',
                approval_order: '',
                current_status_id: '',
                next_status_id: '',
                reject_status_id: '',
                has_email_noty: 'No',
                has_sms_noty: 'No',
                switch_value: 'Active',
            });
        }
        self.approval_level_removal_ids = [];
        self.removeApprovalLevel = function(index, approval_level_id) {
            if (approval_level_id) {
                self.approval_level_removal_ids.push(approval_level_id);
                $('#approval_level_removal_ids').val(JSON.stringify(self.approval_level_removal_ids));
            }
            self.approval_type.approval_levels.splice(index, 1);
        }

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'approval_order': {
                    required: true,
                    number: true,
                    minlength: 1,
                    maxlength: 3,
                },
                'current_status_id': {
                    required: true,
                },
                'next_status_id': {
                    required: true,
                },
                'reject_status_id': {
                    required: true,
                },
            },
            invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 3000)
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('.submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveApprovalTypeLevel'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (!res.success) {
                            $('.submit').prop('disabled', 'disabled');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            $noty = new Noty({
                                type: 'error',
                                layout: 'topRight',
                                text: errors
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $('.submit').button('reset');

                        } else {
                            if (res.comes_from != '') {
                                $noty = new Noty({
                                    type: 'success',
                                    layout: 'topRight',
                                    text: 'Approval Level ' + res.comes_from + ' Successfully',
                                }).show();
                            }
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $('.submit').button('reset');

                            $location.path('/approval-pkg/approval-type/list')
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('.submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 3000);
                    });
            }
        });
    }
});