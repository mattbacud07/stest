$(document).ready(function() {
    // Initialize Select2
    $('#assignedApprover').select2({
        placeholder: "Assign Approver"
    });

    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
      });
});
