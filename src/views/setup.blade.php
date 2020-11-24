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
	        title: 'Verification Flows',
	    }).
	    when('/approval-pkg/approval-type/add', {
	        template: '<approval-type-form></approval-type-form>',
	        title: 'Add Verification Flow',
	    }).
	    when('/approval-pkg/approval-type/edit/:id', {
	        template: '<approval-type-form></approval-type-form>',
	        title: 'Edit Verification Flow',
	    }).
	    when('/approval-pkg/approval-type/view/:id', {
	        template: '<approval-type-view></approval-type-view>',
	        title: 'View Verification Flow',
	    }).

	    //Verification LEVELS
	    when('/approval-pkg/approval-level/list', {
	        template: '<approval-level-list></approval-level-list>',
	        title: 'Verification Levels',
	    }).
	    when('/approval-pkg/approval-level/add', {
	        template: '<approval-level-form></approval-level-form>',
	        title: 'Add Verification Level',
	    }).
	    when('/approval-pkg/approval-level/edit/:id', {
	        template: '<approval-level-form></approval-level-form>',
	        title: 'Edit Verification Level',
	    }).

	    //ENTITY STATUSES
	    when('/approval-pkg/entity-status/list', {
	        template: '<entity-status-list></entity-status-list>',
	        title: 'Entity Statuses',
	    }).
	    when('/approval-pkg/entity-status/add', {
	        template: '<entity-status-form></entity-status-form>',
	        title: 'Add Entity Status',
	    }).
	    when('/approval-pkg/entity-status/edit/:id', {
	        template: '<entity-status-form></entity-status-form>',
	        title: 'Edit Entity Status',
	    }).

	    //VERIFICATION FLOW CONFIGURATION
	    when('/approval-pkg/approval-flow-configuration/list', {
	        template: '<approval-flow-configuration-list></approval-flow-configuration-list>',
	        title: 'Verification Flow Configuration',
	    }).
	    when('/approval-pkg/approval-flow-configuration/add', {
	        template: '<approval-flow-configuration-form></approval-flow-configuration-form>',
	        title: 'Add Verification Flow Configuration',
	    }).
	    when('/approval-pkg/approval-flow-configuration/edit/:id', {
	        template: '<approval-approval-flow-configuration-form></approval-flow-configuration-form>',
	        title: 'Edit Verification Flow Configuration',
	    });

	}]);

    var approval_type_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/list.html')}}";
    var approval_type_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/form.html')}}";
    var approval_type_view_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/view.html')}}";

    var approval_level_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/list.html')}}";
    var approval_level_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/form.html')}}";
    var approval_level_view_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/view.html')}}";

    var entity_status_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/entity-status/list.html')}}";
    var entity_status_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/entity-status/form.html')}}";

    var approval_flow_configuration_list_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-flow-configuration/list.html')}}";
    var approval_flow_configuration_form_template_url = "{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-flow-configuration/form.html')}}";
</script>
<!-- <script type="text/javascript" src="{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-type/controller.js?v=2')}}"></script> -->
<!-- <script type="text/javascript" src="{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/approval-level/controller.js?v=2')}}"></script> -->
<!-- <script type="text/javascript" src="{{URL::asset($approval_pkg_prefix.'/public/themes/'.$theme.'/approval-pkg/entity-status/controller.js?v=2')}}"></script> -->
