<?php

class HL7PrescriptionGenerator
{
    const HL7_SEPARATORS = [
        'field' => '|',
        'component' => '^',
        'subcomponent' => '&',
        'repetition' => '~',
        'escape' => '\\',
    ];

    const SEGMENT_DELIMITER = "\r";

    const HL7_TABLES = [
        "administrative_sex" => [
            "M" => "Male",
            "F" => "Female",
            "U" => "Unknown",
            "A" => "Ambiguous",
            "N" => "Not applicable",
            "O" => "Other"
        ],
        "patient_class" => [
            "E" => "Emergency",
            "I" => "Inpatient",
            "O" => "Outpatient",
            "P" => "Preadmit",
            "R" => "Recurring patient",
            "B" => "Obstetrics",
            "C" => "Commercial Account",
            "N" => "Not Applicable",
            "U" => "Unknown"
        ],
        "order_status" => [
            "A" => "Some, but not all, results available",
            "CA" => "Order was canceled",
            "CM" => "Order is completed",
            "DC" => "Order was discontinued",
            "ER" => "Error, order not found",
            "HD" => "Order is on hold",
            "IP" => "In process, unspecified",
            "RP" => "Order has been replaced",
            "SC" => "In process, scheduled"
        ],
        "priority" => [
            "S" => "Stat",
            "A" => "ASAP",
            "R" => "Routine",
            "P" => "Preoperative",
            "C" => "Callback",
            "T" => "Timing critical"
        ],
        "route" => [
            "PO" => "Oral",
            "IV" => "Intravenous",
            "IM" => "Intramuscular",
            "SC" => "Subcutaneous",
            "INH" => "Inhalation",
            "TOP" => "Topical",
            "PR" => "Rectal",
            "PV" => "Vaginal",
            "SL" => "Sublingual",
            "BUCC" => "Buccal",
            "NAS" => "Nasal",
            "OPH" => "Ophthalmic",
            "OT" => "Otic",
            "TD" => "Transdermal",
            "NG" => "Nasogastric",
            "GT" => "Gastrostomy tube"
        ],
        "units_of_measure" => [
            "TAB" => "Tablet",
            "CAP" => "Capsule",
            "ML" => "Milliliter",
            "MG" => "Milligram",
            "G" => "Gram",
            "MCG" => "Microgram",
            "L" => "Liter",
            "CM" => "Centimeter",
            "KG" => "Kilogram",
            "MEQ" => "Milliequivalent",
            "IU" => "International Unit",
            "HR" => "Hour",
            "DAY" => "Day",
            "WK" => "Week",
            "MO" => "Month"
        ],
        "medication_form" => [
            "TAB" => "Tablet",
            "CAP" => "Capsule",
            "SYR" => "Syrup",
            "SUS" => "Suspension",
            "INJ" => "Injection",
            "CRE" => "Cream",
            "OIN" => "Ointment",
            "SUP" => "Suppository",
            "SOL" => "Solution",
            "POW" => "Powder",
            "GEL" => "Gel",
            "LOT" => "Lotion",
            "AER" => "Aerosol",
            "PAS" => "Paste",
            "FIL" => "Film",
            "IMP" => "Implant"
        ]
    ];

    const MESSAGE_TYPES = [
        'ORM' => 'ORM^O01',
        'ORU' => 'ORU^R01',
        'ADT' => 'ADT^A01',
        'RDE' => 'RDE^O11',
    ];
}

class MedicationItem
{
    public string $medication_code;
    public string $medication_name;
    public string $form;
    public string $strength;
    public float $quantity;
    public string $unit;
    public string $dosage_instruction;
    public string $route;
    public ?int $duration_days;
    public ?int $refills;
    public ?string $special_instructions;
    public ?bool $substitution_allowed;
    public ?string $frequency;
    public ?DateTime $start_datetime;
    public ?DateTime $end_datetime;

    public function __construct(
        string $medication_code,
        string $medication_name,
        string $form,
        string $strength,
        float $quantity,
        string $unit,
        string $dosage_instruction,
        string $route,
        ?int $duration_days = null,
        ?int $refills = null,
        ?string $special_instructions = null,
        ?bool $substitution_allowed = true,
        ?string $frequency = null,
        ?DateTime $start_datetime = null,
        ?DateTime $end_datetime = null
    ) {
        $this->medication_code = $medication_code;
        $this->medication_name = $medication_name;
        $this->form = $form;
        $this->strength = $strength;
        $this->quantity = $quantity;
        $this->unit = $unit;
        $this->dosage_instruction = $dosage_instruction;
        $this->route = $route;
        $this->duration_days = $duration_days;
        $this->refills = $refills;
        $this->special_instructions = $special_instructions;
        $this->substitution_allowed = $substitution_allowed;
        $this->frequency = $frequency;
        $this->start_datetime = $start_datetime;
        $this->end_datetime = $end_datetime;
    }
}

class PatientInfo
{
    public string $patient_id;
    public string $name;
    public DateTime $date_of_birth;
    public string $gender;
    public ?float $weight_kg;
    public ?float $height_cm;
    public array $allergies;
    public array $diagnoses;

    public function __construct(
        string $patient_id,
        string $name,
        DateTime $date_of_birth,
        string $gender,
        ?float $weight_kg = null,
        ?float $height_cm = null,
        ?array $allergies = [],
        ?array $diagnoses = []
    ) {
        $this->patient_id = $patient_id;
        $this->name = $name;
        $this->date_of_birth = $date_of_birth;
        $this->gender = $gender;
        $this->weight_kg = $weight_kg;
        $this->height_cm = $height_cm;
        $this->allergies = $allergies;
        $this->diagnoses = $diagnoses;
    }
}

class PrescribingProvider
{
    public string $id;
    public string $name;
    public ?string $qualification;
    public ?string $specialty;
    public ?string $contact;
    public ?string $address;

    public function __construct(
        string $id,
        string $name,
        ?string $qualification = null,
        ?string $specialty = null,
        ?string $contact = null,
        ?string $address = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->qualification = $qualification;
        $this->specialty = $specialty;
        $this->contact = $contact;
        $this->address = $address;
    }
}

class PharmacyInfo
{
    public string $id;
    public string $name;
    public ?string $address;
    public ?string $contact;

    public function __construct(
        string $id,
        string $name,
        ?string $address = null,
        ?string $contact = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->contact = $contact;
    }
}

class HL7Config
{
    public string $version;
    public string $message_type;
    public string $sending_application;
    public string $sending_facility;
    public string $receiving_application;
    public string $receiving_facility;
    public string $charset;
    public string $country_code;
    public string $processing_id;
    public ?string $message_control_id;
    public bool $include_msh;
    public bool $include_bhs;
    public bool $include_fhs;
    public bool $auto_generate_control_id;
    public int $max_field_length;
    public bool $escape_xml_chars;

    public function __construct(
        string $version = "2.5",
        string $message_type = "RDE",
        string $sending_application = "PRESCRIPTION_SYSTEM",
        string $sending_facility = "HEALTHCARE_PROVIDER",
        string $receiving_application = "PHARMACY_SYSTEM",
        string $receiving_facility = "PHARMACY",
        string $charset = "UTF-8",
        string $country_code = "USA",
        string $processing_id = "P",
        ?string $message_control_id = null,
        bool $include_msh = true,
        bool $include_bhs = false,
        bool $include_fhs = false,
        bool $auto_generate_control_id = true,
        int $max_field_length = 200,
        bool $escape_xml_chars = true
    ) {
        $this->version = $version;
        $this->message_type = HL7PrescriptionGenerator::MESSAGE_TYPES[$message_type] ?? $message_type;
        $this->sending_application = $sending_application;
        $this->sending_facility = $sending_facility;
        $this->receiving_application = $receiving_application;
        $this->receiving_facility = $receiving_facility;
        $this->charset = $charset;
        $this->country_code = $country_code;
        $this->processing_id = $processing_id;
        $this->message_control_id = $message_control_id;
        $this->include_msh = $include_msh;
        $this->include_bhs = $include_bhs;
        $this->include_fhs = $include_fhs;
        $this->auto_generate_control_id = $auto_generate_control_id;
        $this->max_field_length = $max_field_length;
        $this->escape_xml_chars = $escape_xml_chars;
    }
}

class HL7EncodingCharacters
{
    public string $field_separator;
    public string $component_separator;
    public string $repetition_separator;
    public string $escape_character;
    public string $subcomponent_separator;

    public function __construct()
    {
        $this->field_separator = HL7PrescriptionGenerator::HL7_SEPARATORS['field'];
        $this->component_separator = HL7PrescriptionGenerator::HL7_SEPARATORS['component'];
        $this->repetition_separator = HL7PrescriptionGenerator::HL7_SEPARATORS['repetition'];
        $this->escape_character = HL7PrescriptionGenerator::HL7_SEPARATORS['escape'];
        $this->subcomponent_separator = HL7PrescriptionGenerator::HL7_SEPARATORS['subcomponent'];
    }

    public function __toString(): string
    {
        return $this->field_separator . 
               $this->component_separator . 
               $this->repetition_separator . 
               $this->escape_character . 
               $this->subcomponent_separator;
    }
}

class HL7Segment
{
    private string $segment_id;
    private HL7EncodingCharacters $encoding;
    private array $fields = [];

    public function __construct(string $segment_id, HL7EncodingCharacters $encoding)
    {
        $this->segment_id = $segment_id;
        $this->encoding = $encoding;
    }

    public function addField($value, int $position): void
    {
        while (count($this->fields) < $position) {
            $this->fields[] = "";
        }

        if ($value === null) {
            $this->fields[] = "";
        } else {
            $this->fields[] = $this->escapeHL7((string)$value);
        }
    }

    public function setField($value, int $position): void
    {
        if ($position < 1) {
            throw new InvalidArgumentException("Position must be >= 1");
        }

        if (count($this->fields) < $position) {
            $this->addField($value, $position);
        } else {
            if ($value === null) {
                $this->fields[$position - 1] = "";
            } else {
                $this->fields[$position - 1] = $this->escapeHL7((string)$value);
            }
        }
    }

    public function addComponent($value, int $field_pos, int $comp_pos): void
    {
        if ($field_pos < 1 || $comp_pos < 1) {
            throw new InvalidArgumentException("Positions must be >= 1");
        }

        if (count($this->fields) < $field_pos) {
            $this->addField("", $field_pos);
        }

        $field = $this->fields[$field_pos - 1] ?? "";
        $components = $field ? explode($this->encoding->component_separator, $field) : [];

        while (count($components) < $comp_pos) {
            $components[] = "";
        }

        $components[$comp_pos - 1] = $value ? $this->escapeHL7((string)$value) : "";
        $this->fields[$field_pos - 1] = implode($this->encoding->component_separator, $components);
    }

    private function escapeHL7(string $value): string
    {
        if (empty($value)) {
            return "";
        }

        $escape_map = [
            $this->encoding->field_separator => $this->encoding->escape_character . "F" . $this->encoding->escape_character,
            $this->encoding->component_separator => $this->encoding->escape_character . "S" . $this->encoding->escape_character,
            $this->encoding->repetition_separator => $this->encoding->escape_character . "R" . $this->encoding->escape_character,
            $this->encoding->escape_character => $this->encoding->escape_character . "E" . $this->encoding->escape_character,
            $this->encoding->subcomponent_separator => $this->encoding->escape_character . "T" . $this->encoding->escape_character,
        ];

        $result = $value;
        foreach ($escape_map as $char => $escape_seq) {
            $result = str_replace($char, $escape_seq, $result);
        }

        return $result;
    }

    public function build(): string
    {
        $field_str = implode($this->encoding->field_separator, $this->fields);
        return $this->segment_id . $this->encoding->field_separator . $field_str;
    }

    public function getSegmentId(): string
    {
        return $this->segment_id;
    }
}

class HL7Builder
{
    private HL7Config $config;
    private HL7EncodingCharacters $encoding;
    private array $segments = [];
    private string $message_control_id;

    public function __construct(HL7Config $config)
    {
        $this->config = $config;
        $this->encoding = new HL7EncodingCharacters();
        $this->message_control_id = $config->message_control_id ?? $this->generateControlId();
    }

    private function generateControlId(): string
    {
        $timestamp = (new DateTime())->format('YmdHisu');
        return "MSG" . substr($timestamp, 0, -3);
    }

    private function formatHL7Date($dt): string
    {
        if ($dt instanceof DateTime) {
            return $dt->format('YmdHis');
        }
        return "";
    }

    private function formatName(string $name): string
    {
        $parts = explode(' ', $name);
        if (count($parts) >= 2) {
            $last = array_pop($parts);
            $first = array_shift($parts);
            $middle = implode('^', $parts);
            return $last . '^' . $first . ($middle ? '^' . $middle : '');
        }
        return $name;
    }

    public function addMshSegment(): void
    {
        $msh = new HL7Segment("MSH", $this->encoding);

        $msh->addField((string)$this->encoding, 2);
        $msh->addField($this->config->sending_application, 3);
        $msh->addField($this->config->sending_facility, 4);
        $msh->addField($this->config->receiving_application, 5);
        $msh->addField($this->config->receiving_facility, 6);
        $msh->addField($this->formatHL7Date(new DateTime()), 7);
        $msh->addField("", 8);
        $msh->addField($this->config->message_type, 9);
        $msh->addField($this->message_control_id, 10);
        $msh->addField($this->config->processing_id, 11);
        $msh->addField($this->config->version, 12);
        $msh->addField("", 13);
        $msh->addField("", 14);
        $msh->addField("AL", 15);
        $msh->addField("AL", 16);
        $msh->addField($this->config->country_code, 17);
        $msh->addField($this->config->charset, 18);
        $msh->addField("", 19);
        $msh->addField("", 20);

        $this->segments[] = $msh;
    }

    public function addPidSegment(PatientInfo $patient): void
    {
        $pid = new HL7Segment("PID", $this->encoding);

        $pid->addField("1", 1);
        $pid->addField("", 2);
        $pid->addField($patient->patient_id . "^^" . $this->config->sending_facility . "^MR", 3);
        $pid->addField("", 4);
        $pid->addField($this->formatName($patient->name), 5);
        $pid->addField("", 6);
        $pid->addField($this->formatHL7Date($patient->date_of_birth), 7);
        $pid->addField($patient->gender, 8);

        for ($i = 9; $i <= 30; $i++) {
            $pid->addField("", $i);
        }

        $this->segments[] = $pid;

        if ($patient->weight_kg !== null) {
            $obx = new HL7Segment("OBX", $this->encoding);
            $obxCount = count(array_filter($this->segments, fn($s) => $s->getSegmentId() === "OBX")) + 1;
            
            $obx->addField((string)$obxCount, 1);
            $obx->addField("NM", 2);
            $obx->addField("3141-9^Body weight Measured^LN", 3);
            $obx->addField("", 4);
            $obx->addField((string)$patient->weight_kg, 5);
            $obx->addField("kg", 6);
            $obx->addField("", 7);
            $obx->addField("", 8);
            $obx->addField("", 9);
            $obx->addField("", 10);
            $obx->addField("F", 11);
            $obx->addField("", 12);
            $obx->addField("", 13);
            $obx->addField($this->formatHL7Date(new DateTime()), 14);
            $obx->addField("", 15);
            $obx->addField("", 16);

            $this->segments[] = $obx;
        }

        if ($patient->height_cm !== null) {
            $obx = new HL7Segment("OBX", $this->encoding);
            $obxCount = count(array_filter($this->segments, fn($s) => $s->getSegmentId() === "OBX")) + 1;
            
            $obx->addField((string)$obxCount, 1);
            $obx->addField("NM", 2);
            $obx->addField("8302-2^Body height^LN", 3);
            $obx->addField("", 4);
            $obx->addField((string)$patient->height_cm, 5);
            $obx->addField("cm", 6);
            $obx->addField("", 7);
            $obx->addField("", 8);
            $obx->addField("", 9);
            $obx->addField("", 10);
            $obx->addField("F", 11);
            $obx->addField("", 12);
            $obx->addField("", 13);
            $obx->addField($this->formatHL7Date(new DateTime()), 14);
            $obx->addField("", 15);
            $obx->addField("", 16);

            $this->segments[] = $obx;
        }
    }

    public function addPv1Segment(string $patient_class = "O"): void
    {
        $pv1 = new HL7Segment("PV1", $this->encoding);

        $pv1->addField("1", 1);
        $pv1->addField($patient_class, 2);

        for ($i = 3; $i <= 50; $i++) {
            $pv1->addField("", $i);
        }

        $this->segments[] = $pv1;
    }

    public function addOrcSegment(
        string $order_control = "NW",
        string $placer_order_number = "",
        string $filler_order_number = "",
        string $order_status = "SC",
        string $response_flag = "",
        ?array $timing_quantity = null,
        string $parent_order = "",
        ?DateTime $datetime_of_transaction = null,
        ?PrescribingProvider $entered_by = null,
        ?PrescribingProvider $verified_by = null,
        ?PrescribingProvider $ordering_provider = null
    ): void {
        $orc = new HL7Segment("ORC", $this->encoding);

        $orc->addField($order_control, 1);
        $orc->addField($placer_order_number, 2);
        $orc->addField($filler_order_number, 3);
        $orc->addField("", 4);
        $orc->addField($order_status, 5);
        $orc->addField($response_flag, 6);

        if ($timing_quantity) {
            $orc->addField(implode($this->encoding->component_separator, $timing_quantity), 7);
        }

        $orc->addField($parent_order, 8);
        $orc->addField(
            $datetime_of_transaction ? $this->formatHL7Date($datetime_of_transaction) : $this->formatHL7Date(new DateTime()),
            9
        );

        if ($entered_by) {
            $orc->addField($entered_by->name . "^" . $entered_by->id, 10);
        }

        if ($verified_by) {
            $orc->addField($verified_by->name . "^" . $verified_by->id, 11);
        }

        if ($ordering_provider) {
            $orc->addField($ordering_provider->name . "^" . $ordering_provider->id, 12);
        }

        for ($i = 13; $i <= 16; $i++) {
            $orc->addField("", $i);
        }

        $this->segments[] = $orc;
    }

    public function addRxeSegment(
        MedicationItem $medication,
        string $give_per = "DOSE",
        ?string $give_rate = null,
        ?string $give_units = null,
        ?string $give_strength = null,
        ?string $give_strength_units = null,
        ?string $provider_administration_instructions = null,
        ?string $delivery_administration_instructions = null
    ): void {
        $rxe = new HL7Segment("RXE", $this->encoding);

        $timing = [];
        if ($medication->frequency) {
            $timing[] = $medication->frequency;
        }
        if ($medication->start_datetime) {
            $timing[] = $this->formatHL7Date($medication->start_datetime);
        }
        if ($medication->duration_days) {
            $timing[] = (string)$medication->duration_days;
            $timing[] = "D";
        }

        $rxe->addField($timing ? implode($this->encoding->component_separator, $timing) : "", 1);
        $rxe->addField($medication->medication_code . "^" . $medication->medication_name . "^NDC", 2);
        $rxe->addField((string)$medication->quantity, 3);
        $rxe->addField("", 4);
        $rxe->addField($medication->unit, 5);
        
        $form = HL7PrescriptionGenerator::HL7_TABLES["medication_form"][$medication->form] ?? $medication->form;
        $rxe->addField($form, 6);

        $admin_instructions = $medication->dosage_instruction;
        if ($medication->special_instructions) {
            $admin_instructions .= "; " . $medication->special_instructions;
        }
        $rxe->addField($admin_instructions, 7);

        $rxe->addField("", 8);
        $rxe->addField($medication->substitution_allowed ? "G" : "N", 9);
        $rxe->addField((string)$medication->quantity, 10);
        $rxe->addField($medication->unit, 11);
        $rxe->addField($medication->refills ? (string)$medication->refills : "0", 12);

        for ($i = 13; $i <= 21; $i++) {
            $rxe->addField("", $i);
        }

        $rxe->addField($give_per, 22);
        $rxe->addField($give_rate, 23);
        $rxe->addField($give_units, 24);
        $rxe->addField($give_strength, 25);
        $rxe->addField($give_strength_units, 26);

        for ($i = 27; $i <= 30; $i++) {
            $rxe->addField("", $i);
        }

        $this->segments[] = $rxe;
        $this->addRxrSegment($medication->route);
    }

    public function addRxrSegment(string $route, ?string $site = null): void
    {
        $rxr = new HL7Segment("RXR", $this->encoding);

        $route_desc = HL7PrescriptionGenerator::HL7_TABLES["route"][$route] ?? $route;
        $rxr->addField($route . "^" . $route_desc . "^HL70162", 1);

        if ($site) {
            $rxr->addField($site, 2);
        }

        for ($i = 3; $i <= 6; $i++) {
            $rxr->addField("", $i);
        }

        $this->segments[] = $rxr;
    }

    public function addRxdSegment(
        MedicationItem $medication,
        int $dispense_number = 1,
        ?float $quantity_dispensed = null,
        ?DateTime $fill_datetime = null,
        ?int $days_supply = null
    ): void {
        $rxd = new HL7Segment("RXD", $this->encoding);

        $rxd->addField((string)$dispense_number, 1);
        $rxd->addField($medication->medication_code . "^" . $medication->medication_name . "^NDC", 2);
        $rxd->addField(
            $fill_datetime ? $this->formatHL7Date($fill_datetime) : $this->formatHL7Date(new DateTime()),
            3
        );
        $rxd->addField((string)($quantity_dispensed ?? $medication->quantity), 4);
        $rxd->addField($medication->unit, 5);
        
        $form = HL7PrescriptionGenerator::HL7_TABLES["medication_form"][$medication->form] ?? $medication->form;
        $rxd->addField($form, 6);

        $rxd->addField("", 7);
        $rxd->addField($medication->refills ? (string)$medication->refills : "0", 8);
        $rxd->addField("", 9);
        $rxd->addField("", 10);
        $rxd->addField($medication->substitution_allowed ? "G" : "N", 11);

        for ($i = 12; $i <= 15; $i++) {
            $rxd->addField("", $i);
        }

        $rxd->addField($medication->strength, 16);

        for ($i = 17; $i <= 38; $i++) {
            $rxd->addField("", $i);
        }

        $this->segments[] = $rxd;
    }

    public function addDiagnosisSegments(array $diagnoses): void
    {
        foreach ($diagnoses as $idx => $diagnosis) {
            $dg1 = new HL7Segment("DG1", $this->encoding);
            $dg1->addField((string)($idx + 1), 1);
            $dg1->addField("I10", 2);
            
            if (is_array($diagnosis) && count($diagnosis) >= 2) {
                $dg1->addField($diagnosis[0] . "^" . $diagnosis[1] . "^I10", 3);
            } else {
                $dg1->addField($diagnosis . "^" . $diagnosis . "^I10", 3);
            }
            
            $dg1->addField("", 4);
            $dg1->addField($this->formatHL7Date(new DateTime()), 5);
            $dg1->addField("W", 6);

            for ($i = 7; $i <= 21; $i++) {
                $dg1->addField("", $i);
            }

            $this->segments[] = $dg1;
        }
    }

    public function addAllergySegments(array $allergies): void
    {
        foreach ($allergies as $idx => $allergy) {
            $al1 = new HL7Segment("AL1", $this->encoding);
            $al1->addField((string)($idx + 1), 1);
            $al1->addField("DA", 2);
            $al1->addField($allergy, 3);

            for ($i = 4; $i <= 6; $i++) {
                $al1->addField("", $i);
            }

            $this->segments[] = $al1;
        }
    }

    public function addNteSegment(string $comment, int $set_id = 1, string $source = "P"): void
    {
        $nte = new HL7Segment("NTE", $this->encoding);
        $nte->addField((string)$set_id, 1);
        $nte->addField($source, 2);
        $nte->addField($comment, 3);
        $this->segments[] = $nte;
    }

    public function buildMessage(): string
    {
        if ($this->config->include_msh) {
            $hasMsh = false;
            foreach ($this->segments as $segment) {
                if ($segment->getSegmentId() === "MSH") {
                    $hasMsh = true;
                    break;
                }
            }
            if (!$hasMsh) {
                $this->addMshSegment();
            }
        }

        $segments_str = array_map(fn($s) => $s->build(), $this->segments);
        return implode(HL7PrescriptionGenerator::SEGMENT_DELIMITER, $segments_str);
    }
}

class HL7Converter
{
    public static function convertEdifactToHL7(array $edifact_data): array
    {
        $patient = new PatientInfo(
            $edifact_data["patient"]["patient_id"],
            $edifact_data["patient"]["name"],
            DateTime::createFromFormat('Ymd', $edifact_data["patient"]["date_of_birth"]),
            $edifact_data["patient"]["gender"],
            isset($edifact_data["patient"]["weight_kg"]) ? (float)$edifact_data["patient"]["weight_kg"] : null,
            isset($edifact_data["patient"]["height_cm"]) ? (float)$edifact_data["patient"]["height_cm"] : null,
            $edifact_data["patient"]["allergies"] ?? [],
            array_map(fn($d) => [$d, ""], $edifact_data["patient"]["diagnoses"] ?? [])
        );

        $provider = new PrescribingProvider(
            $edifact_data["prescribing_doctor"]["id"],
            $edifact_data["prescribing_doctor"]["name"],
            $edifact_data["prescribing_doctor"]["qualification"] ?? null,
            $edifact_data["prescribing_doctor"]["specialty"] ?? null,
            $edifact_data["prescribing_doctor"]["contact"] ?? null,
            $edifact_data["prescribing_doctor"]["address"] ?? null
        );

        $pharmacy = new PharmacyInfo(
            $edifact_data["pharmacy"]["id"],
            $edifact_data["pharmacy"]["name"],
            $edifact_data["pharmacy"]["address"] ?? null,
            $edifact_data["pharmacy"]["contact"] ?? null
        );

        $medications = array_map(function($item) {
            return new MedicationItem(
                $item["medication_code"],
                $item["medication_name"],
                $item["form"],
                $item["strength"],
                (float)$item["quantity"],
                $item["unit"] ?? $item["form"],
                $item["dosage_instruction"],
                $item["route"],
                $item["duration_days"] ?? null,
                $item["refills"] ?? null,
                $item["special_instructions"] ?? null,
                $item["substitution_allowed"] ?? true,
                "QD"
            );
        }, $edifact_data["items"]);

        $prescription_info = [
            "prescription_id" => $edifact_data["prescription_id"],
            "prescription_date" => DateTime::createFromFormat('Ymd', $edifact_data["prescription_date"]),
            "urgent" => $edifact_data["urgent"] ?? false,
            "validity_days" => $edifact_data["validity_days"] ?? null,
            "payment_type" => $edifact_data["payment_type"] ?? null,
            "insurance_info" => $edifact_data["insurance_info"] ?? null,
            "clinical_notes" => $edifact_data["clinical_notes"] ?? null,
            "dispense_as_written" => !($edifact_data["substitution_allowed"] ?? true)
        ];

        return [
            "patient" => $patient,
            "provider" => $provider,
            "pharmacy" => $pharmacy,
            "medications" => $medications,
            "prescription_info" => $prescription_info
        ];
    }

    public static function createHL7Prescription(array $hl7_data, ?HL7Config $config = null): string
    {
        if ($config === null) {
            $config = new HL7Config();
        }

        $builder = new HL7Builder($config);
        
        $builder->addMshSegment();
        $builder->addPidSegment($hl7_data["patient"]);
        $builder->addPv1Segment("O");
        
        $builder->addOrcSegment(
            "NW",
            $hl7_data["prescription_info"]["prescription_id"],
            "",
            "SC",
            "",
            null,
            "",
            $hl7_data["prescription_info"]["prescription_date"],
            null,
            null,
            $hl7_data["provider"]
        );
        
        if (!empty($hl7_data["patient"]->diagnoses)) {
            $builder->addDiagnosisSegments($hl7_data["patient"]->diagnoses);
        }
        
        if (!empty($hl7_data["patient"]->allergies)) {
            $builder->addAllergySegments($hl7_data["patient"]->allergies);
        }
        
        if (!empty($hl7_data["prescription_info"]["clinical_notes"])) {
            $builder->addNteSegment($hl7_data["prescription_info"]["clinical_notes"], 1, "P");
        }
        
        foreach ($hl7_data["medications"] as $idx => $medication) {
            $builder->addRxeSegment($medication);
            
            if ($config->message_type === HL7PrescriptionGenerator::MESSAGE_TYPES["RDE"]) {
                $builder->addRxdSegment($medication, $idx + 1);
            }
        }
        
        return $builder->buildMessage();
    }

    public static function parseHL7Response(string $hl7_message): array
    {
        $lines = explode(HL7PrescriptionGenerator::SEGMENT_DELIMITER, $hl7_message);
        $result = [
            "segments" => [],
            "acknowledgment" => null,
            "status" => "unknown"
        ];
        
        foreach ($lines as $line) {
            if (strpos($line, "MSH") === 0) {
                $parts = explode("|", $line);
                $result["message_type"] = $parts[8] ?? "";
                $result["message_control_id"] = $parts[9] ?? "";
            } elseif (strpos($line, "MSA") === 0) {
                $parts = explode("|", $line);
                if (count($parts) >= 2) {
                    $result["acknowledgment"] = [
                        "code" => $parts[1],
                        "message" => $parts[2] ?? "",
                        "control_id" => $parts[3] ?? ""
                    ];
                    
                    switch ($parts[1]) {
                        case "AA":
                            $result["status"] = "accepted";
                            break;
                        case "AE":
                            $result["status"] = "error";
                            break;
                        case "AR":
                            $result["status"] = "rejected";
                            break;
                    }
                }
            }
            
            if ($line) {
                $result["segments"][] = substr($line, 0, 3);
            }
        }
        
        return $result;
    }
}

function main(): void
{
    $edifact_prescription = [
        "message_ref" => "MED0001",
        "prescription_id" => "RX2025-0509-001",
        "prescription_date" => "20241210",
        "urgent" => false,
        "validity_days" => 30,
        "payment_type" => "INSURANCE",
        "insurance_info" => [
            "id" => "INS123456789",
            "name" => "HealthCare Plus"
        ],
        "dispense_as_written" => false,
        "clinical_notes" => "Patient has history of mild hypertension. Monitor blood pressure during treatment.",
        "prescribing_doctor" => [
            "id" => "DOC987654321",
            "name" => "Dr. Jane Smith",
            "qualification" => "MD",
            "specialty" => "Internal Medicine",
            "contact" => "+1-555-123-4567",
            "address" => "123 Medical Center, Suite 100"
        ],
        "patient" => [
            "patient_id" => "PAT123456789",
            "name" => "John Doe",
            "date_of_birth" => "19800515",
            "gender" => "M",
            "weight_kg" => "85.5",
            "height_cm" => "180.0",
            "allergies" => ["Penicillin", "Sulfa drugs"],
            "diagnoses" => ["I10", "E11.9"]
        ],
        "pharmacy" => [
            "id" => "PHARM12345",
            "name" => "City Pharmacy",
            "address" => "456 Main Street",
            "contact" => "+1-555-987-6543"
        ],
        "items" => [
            [
                "medication_code" => "C09AA01",
                "medication_name" => "Lisinopril",
                "form" => "TAB",
                "strength" => "10 mg",
                "quantity" => "30",
                "unit" => "TAB",
                "dosage_instruction" => "Take 1 tablet once daily in the morning",
                "route" => "PO",
                "duration_days" => 30,
                "refills" => 3,
                "special_instructions" => "Take with food if stomach upset occurs",
                "substitution_allowed" => true
            ]
        ]
    ];
    
    try {
        echo "Converting EDIFACT to HL7 format...\n";
        $hl7_data = HL7Converter::convertEdifactToHL7($edifact_prescription);
        
        $config = new HL7Config(
            "2.5",
            "RDE",
            "EDIFACT_CONVERTER",
            "HOSPITAL_XYZ",
            "PHARMACY_SYSTEM",
            "PHARMACY_ABC",
            "UTF-8",
            "USA",
            "P"
        );
        
        echo "Generating HL7 message...\n";
        $hl7_message = HL7Converter::createHL7Prescription($hl7_data, $config);
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "HL7 PRESCRIPTION MESSAGE (RDE^O11)\n";
        echo str_repeat("=", 80) . "\n";
        echo $hl7_message . "\n";
        
        file_put_contents("prescription.hl7", $hl7_message);
        echo "HL7 message saved to prescription.hl7\n";
        
        $segments = explode(HL7PrescriptionGenerator::SEGMENT_DELIMITER, $hl7_message);
        echo "\nTotal segments: " . count($segments) . "\n";
        
        $segment_types = [];
        foreach ($segments as $segment) {
            if ($segment) {
                $segment_types[] = substr($segment, 0, 3);
            }
        }
        $segment_types = array_unique($segment_types);
        sort($segment_types);
        echo "Segment types: " . implode(", ", $segment_types) . "\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
}

if (PHP_SAPI === 'cli') {
    main();
}
