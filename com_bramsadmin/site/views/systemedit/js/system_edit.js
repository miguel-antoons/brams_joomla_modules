/* global $ */
/* global currentId */
/* global locationAntennas */
function newSystem(form) {
    const antennaValue = form.systemAntenna.value;
    const locationSelect = form.systemLocation;
    const locationId = locationSelect.value;
    const systemName = form.systemName.value;
    const systemStart = form.systemStart.value;

    if (!antennaValue || !locationId || !systemName || !systemStart) {
        document.getElementById('error').innerHTML = `
            Please fill all required inputs before submitting the form. 
            Required inputs are Name, Location, Antenna and Start.
        `;

        return false;
    }

    if (locationAntennas[locationId].includes(Number(antennaValue))) {
        document.getElementById('error').innerHTML = `
            Antenna - location combo ${antennaValue} - ${locationId} (
            ${locationSelect.options[locationSelect.selectedIndex].label}) 
            already exists. Either set a different antenna value (recommended is 
            ${locationAntennas[locationId][locationAntennas[locationId].length - 1] + 1}) 
            or change system location.
        `;

        return false;
    }

    $.ajax({
        type: 'POST',
        url: '/index.php?option=com_bramsadmin&view=systemedit&task=newsystem&format=json',
        data: {
            newSystemInfo: {
                name: form.systemName.value,
                location: locationId,
                antenna: antennaValue,
                start: form.systemStart.value,
                comments: form.systemComments.value,
            },
        },
        success: () => {
            window.location.href = '/index.php?option=com_bramsadmin&view=systems';
        },
        error: (response) => {
            console.log('api call failed', '\n', response);
        },
    });
}

function updateSystem(form) {
    const formT = form + 1;
    return formT;
}

function formProcess(form) {
    if (currentId) {
        return updateSystem(form);
    }

    return newSystem(form);
}

function setAntenna() {
    const selectedLocation = document.getElementById('systemLocation').value;
    locationAntennas[String(selectedLocation)].sort();
    document.getElementById('systemAntenna').value = (
        locationAntennas[selectedLocation][locationAntennas[selectedLocation].length - 1]
        + 1
    );
}
