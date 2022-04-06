/* global $ */
// eslint-disable-next-line no-unused-vars
let log = 'Nothing to show';        // contains debug information if needed
let receiverId = 0;                  // the id of the receiver to show (if 0 --> no receiver)
let receiverCodes = [];              // array with all receiver codes

/**
 * Function checks if all the required inputs have values in them. It doesn't
 * check the values itself, just if there is something.
 *
 * @param receiverCode  {string}    receiverCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if an input is empty
 */
function verifyRequired(receiverCode, oldIsValid) {
    // if one of the required inputs are empty
    if (!receiverCode) {
        // add an exclamation circle to the required inputs
        const requiredInputs = document.getElementsByClassName('required');
        Array.from(requiredInputs).forEach(
            (input) => {
                input.innerHTML
                    += '<i class="fa fa-exclamation-circle orange right" aria-hidden="true"></i>';
            },
        );

        // add an error text
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle orange" aria-hidden="true"></i>
                Please fill all required inputs before submitting the form. 
                Required inputs are Receiver Code.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered receiver code doesn't exist already.
 * This is done so that each receiver code is unique.
 *
 * @param receiverCode  {string}    receiverCode input value
 * @param oldIsValid    {boolean}   flag that determines if values are valid or not
 * @returns             {(*|string)[]|(boolean|string)[]}
 *                                  Returns an array with 2 values :
 *                                      0: new isValid flag
 *                                      1: error message if the receiver code already exists
 */
function verifyCode(receiverCode, oldIsValid) {
    const pattern = /^[a-z\d\-_]+$/i;
    // if the receiver code already exists
    if (receiverCodes.includes(receiverCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered receiver code is already taken. Please enter a free receiver code.
            </li>`,
        ];
    }

    // test if any forbidden characters are in the code
    if (!pattern.test(receiverCode)) {
        document.getElementById('code').innerHTML += ''
            + '<i class="fa fa-exclamation-circle red right" aria-hidden="true"></i>';

        // set the valid flag to false and return an error message
        return [
            false,
            `<li>
                <i class="fa fa-exclamation-circle red" aria-hidden="true"></i>
                Entered receiver code contains forbidden characters. Be sure to only use dash, 
                underscore and alphanumeric characters.
            </li>`,
        ];
    }

    return [oldIsValid, ''];
}

/**
 * Function verifies if the entered values are valid upon api call. If all the
 * values are valid, the function returns true. If not it returns false.
 *
 * @param receiverCode  {string}    receiverCode input value
 * @returns             {boolean}   true if the inputs are valid, else returns false
 */
function verifyValues(receiverCode) {
    // remove all the icons inside the labels
    const icons = document.querySelectorAll('label .fa');
    let verificationResult;
    icons.forEach((icon) => icon.remove());

    let isValid = true;
    let HTMLError = '<h4>Found Problems</h4><ul>';

    // check if all required inputs are filled
    verificationResult = verifyRequired(receiverCode, isValid);
    [isValid] = verificationResult;
    HTMLError += verificationResult[1];

    // check if receiver code is valid
    verificationResult = verifyCode(receiverCode, isValid);
    [isValid] = verificationResult;

    // display the errors on the page
    document.getElementById('error').innerHTML = `${HTMLError}${verificationResult[1]}</ul>`;

    return isValid;
}

/**
 * Function calls an api to create a new receiver. It sends all the information
 * about the new receiver to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function newReceiver(formInputs) {
    const recCode = formInputs.receiverCode.value;
    const recBrand = formInputs.receiverBrand.value;
    const recModel = formInputs.receiverModel.value;
    const recComments = formInputs.receiverComments.value;

    // verify if the entered values are valid
    if (verifyValues(recCode)) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &task=new
                &view=receiverEdit
                &format=json
                &${token}=1
            `,
            data: {
                new_receiver: {
                    code: recCode,
                    brand: recBrand,
                    model: recModel,
                    comments: recComments,
                },
            },
            success: () => {
                // on success return to the antennas page
                window.location.href = '/index.php?option=com_bramsadmin&view=receivers&message=2';
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('error').innerHTML = (
                    'API call failed, please read the \'log\' variable in '
                    + 'developer console for more information about the problem.'
                );
                // store the server response in the log variable
                log = response;
            },
        });
    }
}

/**
 * Function calls an api to update a receiver. It sends all the information
 * about the updated receiver to back-end. For the api to be called, the input values
 * have to be valid.
 *
 * @param formInputs    {HTMLDivElement.children}    Div element containing the inputs
 */
function updateReceiver(formInputs) {
    const recCode = formInputs.receiverCode.value;
    const recBrand = formInputs.receiverBrand.value;
    const recModel = formInputs.receiverModel.value;
    const recComments = formInputs.receiverComments.value;

    // verify if all the entered values are valid
    if (verifyValues(recCode)) {
        const token = $('#token').attr('name');
        $.ajax({
            type: 'POST',
            url: `
                /index.php?
                option=com_bramsadmin
                &task=update
                &view=receiverEdit
                &format=json
                &${token}=1
            `,
            data: {
                modified_receiver: {
                    id: receiverId,
                    code: recCode,
                    brand: recBrand,
                    model: recModel,
                    comments: recComments,
                },
            },
            success: () => {
                // on success return to the receivers page
                window.location.href = '/index.php?option=com_bramsadmin&view=receivers&message=1';
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('error').innerHTML = (
                    'API call failed, please read the \'log\' variable in '
                    + 'developer console for more information about the problem.'
                );
                // store the server response in the log variable
                log = response;
            },
        });
    }
}

// function decides which api to call (update or create)
function formProcess(form) {
    if (receiverId) {
        return updateReceiver(form);
    }
    return newReceiver(form);
}

/**
 * Function gets all the taken receiver codes via an api call to
 * the sites back-end. This function exists in order to verify if
 * the entered receiver code hasn't been taken yet.
 */
function getReceiverCodes() {
    const token = $('#token').attr('name');

    $.ajax({
        type: 'GET',
        url: `
            /index.php?
            option=com_bramsadmin
            &task=getCodes
            &view=receiverEdit
            &format=json
            &receiverId=${receiverId}
            &${token}=1
        `,
        success: (response) => {
            // store the receiver codes locally
            receiverCodes = response.data;
        },
        error: (response) => {
            // on fail, show an error message
            document.getElementById('error').innerHTML = (
                'API call failed, please read the \'log\' variable in '
                + 'developer console for more information about the problem.'
            );
            // store the server response in the log variable
            log = response;
        },
    });
}

/**
 * Function gets all the information about a specific receiver through
 * an api to the sites back-end. The receiver to get information from
 * is specified in the pages url.
 * It then prepares the page to update/create a receiver.
 */
function getReceiverInfo() {
    // get the id from url
    const paramString = window.location.search.split('?')[1];
    const queryString = new URLSearchParams(paramString);
    receiverId = Number(queryString.get('id'));
    // get all the receiver codes
    getReceiverCodes();

    // if a receiver has to be updated
    if (receiverId) {
        const token = $('#token').attr('name');

        $.ajax({
            type: 'GET',
            url: `
                index.php?
                option=com_bramsadmin
                &task=getOne
                &view=receiverEdit
                &format=json
                &id=${receiverId}
                &${token}=1
            `,
            success: (response) => {
                document.getElementById('receiverCode').value = response.data.code;
                document.getElementById('receiverBrand').value = response.data.brand;
                document.getElementById('receiverModel').value = response.data.model;
                document.getElementById('receiverComments').value = response.data.comments;
                document.getElementById('title').innerHTML = `
                    Update Receiver ${response.data.code}`;
            },
            error: (response) => {
                // on fail, show an error message
                document.getElementById('error').innerHTML = (
                    'API call failed, please read the \'log\' variable in '
                    + 'developer console for more information about the problem.'
                );
                // store the server response in the log variable
                log = response;
            },
        });
    }
}

// set onload function
window.onload = getReceiverInfo;
