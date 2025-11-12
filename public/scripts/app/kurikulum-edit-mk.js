document.addEventListener("livewire:initialized", () => {
    // Select2 - Add Program Studi Pengampu
    // ------------------
    $('#selectAddProgramStudiPengampu').select2({
        dropdownParent: $('#modalMatakuliahAdd'),
        theme: "bootstrap-5",
        placeholder: "--- Pilih ---",
    }).on('change', function(e) {
        const value = $(this).val();
        @this.addProgramStudiPengampu = value;
    });

    // Select2 - Add Kelompok Keahlian
    // ------------------
    $('#selectAddKKId').select2({
        dropdownParent: $('#modalMatakuliahAdd'),
        theme: "bootstrap-5",
        placeholder: "--- Pilih ---",
    }).on('change', function(e) {
        const value = $(this).val();
        @this.addKKId = value;
    });

    // Add Materi Pembelajaran
    // ------------------
    const quillAddMateriPembelajaran = new Quill("#editorAddMateriPembelajaran", {
        theme: "snow",
    });
    quillAddMateriPembelajaran.on('text-change', function() {
        const value = quillAddMateriPembelajaran.root.innerHTML;
        @this.addMateriPembelajaran = value;
    });

    // Add Catatan
    // ------------------
    const quillAddCatatan = new Quill("#editorAddCatatan", {
        theme: "snow",
    });
    quillAddCatatan.on('text-change', function() {
        const value = quillAddCatatan.root.innerHTML;
        @this.addCatatan = value;
    });


    // Select2 - Edit Program Studi Pengampu
    // ------------------
    $('#selectEditProgramStudiPengampu').select2({
        dropdownParent: $('#modalMatakuliahEdit'),
        theme: "bootstrap-5",
        placeholder: "--- Pilih ---",
    }).on('change', function(e) {
        const value = $(this).val();
        @this.editProgramStudiPengampu = value;
    });

    // Select2 - Edit Kelompok Keahlian
    // ------------------
    $('#selectEditKKId').select2({
        dropdownParent: $('#modalMatakuliahEdit'),
        theme: "bootstrap-5",
        placeholder: "--- Pilih ---",
    }).on('change', function(e) {
        const value = $(this).val();
        @this.editKKId = value;
    });

    // Edit Materi Pembelajaran
    // ------------------
    const quillAddMateriPembelajaran = new Quill("#editorEditMateriPembelajaran", {
        theme: "snow",
    });
    quillEditMateriPembelajaran.on('text-change', function() {
        const value = quillEditMateriPembelajaran.root.innerHTML;
        @this.editMateriPembelajaran = value;
    });

    // Edit Catatan
    // ------------------
    const quillEditCatatan = new Quill("#editorEditCatatan", {
        theme: "snow",
    });
    quillEditCatatan.on('text-change', function() {
        const value = quillEditCatatan.root.innerHTML;
        @this.editCatatan = value;
    });

    // Prepare Edit Data
    // ------------------
    Livewire.on("prepareEditData", (data) => {
        // Set data to Quill editors
        quillEditCatatan.root.innerHTML = data.catatan;
        quillEditMateriPembelajaran.root.innerHTML = data.materi_pembelajaran;

        // Set data to select2
        $('#selectEditProgramStudiPengampu').val(data.programStudiPengampu).trigger('change');
        $('#selectEditKKId').val(data.kk_id).trigger('change');
    });
});