<html>
    <head>
        <title>Gett address api</title>
        <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="https://ws1.postcodesoftware.co.uk/lookup.min.js"></script>

</head>
<body>
<!-- Postcode Field -->
<div id="postcode_lookup"></div>
<!-- Address Fields -->
<input id="organisation" type="text" placeholder="Organisation" />
<input id="line1" type="text" placeholder="Address Line 1" />
<input id="line2" type="text" placeholder="Address Line 2" />
<input id="line3" type="text" placeholder="Address Line 3" />
<input id="town" type="text" placeholder="Town" />
<input id="postcode" type="text" placeholder="Postcode" />


<!-- Add below form -->
<script>
    $('#postcode_lookup').getAddress({
        api_key: 'XxTK-LOIC-nFz2-lHYF',
        // Prevents the organisation from appearing within address line1
        line1_no_organisation: 'true',
        output_fields: {
            organisation: '#organisation',
            line1: '#line1',
            line2: '#line2',
            line3: '#line3',
            town: '#town',
            postcode: '#postcode',
        }
    })
</script>
</body>
</html>

