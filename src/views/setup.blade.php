@if(config('approval-pkg.DEV'))
    <?php $approval_pkg_prefix = '/packages/abs/approval-pkg/src';?>
@else
    <?php $approval_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    //APPROVAL TYPE
	    when('/approval-pkg/approval-type/list', {
	        template: '<approval-type-list></approval-type-list>',
	        title: 'Approval Types',
	    }).
	    when('/approval-pkg/approval-type/add', {
	        template: '<approval-type-form></approval-type-form>',
	        title: 'Add Approval Type',
	    }).
	    when('/approval-pkg/approval-type/edit/:id', {
	        template: '<approval-type-form></approval-type-form>',
	        title: 'Edit Approval Type',
	    });
	}]);

    var approval_type_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/list.html')}}";
    var approval_type_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/form.html')}}";
</script>
<script type="text/javascript" src="{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/controller.js?v=2')}}"></script>