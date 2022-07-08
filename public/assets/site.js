$(document).ready(function () {
    $("#Products").on("change", function () {
        $list = $("#PermissionProfilesFiltered");

        let json_raw = $("#PermissionProfilesData").attr("data-value"),
            json = json_raw ? JSON.parse(json_raw) : false;

        if (json) {
            $list.empty();
            $.each(json, function (key, product) {
                if(key === $("#Products option:selected").html()){
                    $.each(product, function (i, permissionProfile) {
                        $list.append(
                            '<option value="' 
                            + permissionProfile["permission_profile_id"] 
                            + '"> ' 
                            + permissionProfile["permission_profile_name"] 
                            + ' </option>'
                        );
                    });
                }
            });
        }
    });
});
