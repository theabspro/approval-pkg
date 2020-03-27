@if(config('approval-pkg.DEV'))
    <?php $approval_pkg_prefix = '/packages/abs/approval-pkg/src';?>
@else
    <?php $approval_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    //APPROVAL FLOW
	    when('/approval-pkg/approval-type/list', {
	        template: '<approval-type-list></approval-type-list>',
	        title: 'Approval Flows',
	    }).
	    when('/approval-pkg/approval-type/add', {
	        template: '<approval-type-form></approval-type-form>',
	        title: 'Add Approval Flow',
	    }).
	    when('/approval-pkg/approval-type/edit/:id', {
	        template: '<approval-type-form></approval-type-form>',
	        title: 'Edit Approval Flow',
	    }).
	    when('/approval-pkg/approval-type/view/:id', {
	        template: '<approval-type-view></approval-type-view>',
	        title: 'View Approval Flow',
	    }).

	    //APPROVAL LEVELS
	    when('/approval-pkg/approval-level/list', {
	        template: '<approval-level-list></approval-level-list>',
	        title: 'Approval Levels',
	    }).
	    when('/approval-pkg/approval-level/add', {
	        template: '<approval-level-form></approval-level-form>',
	        title: 'Add Approval Level',
	    }).
	    when('/approval-pkg/approval-level/edit/:id', {
	        template: '<approval-level-form></approval-level-form>',
	        title: 'Edit Approval Level',
	    });

	}]);

    var approval_type_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/list.html')}}";
    var approval_type_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/form.html')}}";
    var approval_type_view_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/view.html')}}";

    var approval_level_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/list.html')}}";
    var approval_level_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/form.html')}}";
    var approval_level_view_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/view.html')}}";
</script>
<script type="text/javascript" src="{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/controller.js?v=2')}}"></script>
