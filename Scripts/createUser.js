document.getElementById('soummission_btn').addEventListener('click', creerUser);
async function creerUser(event) {
    event.preventDefault();
    // Get the form and the submit button
    const form = document.querySelector('#form_new_user_id');

    // Create a FormData object from the form
    const formData = new FormData(form);
    //console.log(formData.values);

    try {
        const response = await fetch('http://localhost:8000/api/creerUser', {
            method: 'POST',
            body: formData,
        });

        // Traitement de la reponse
        if (response.ok) {
            const result = await response.json();
            console.log(result);
            //form.reset(); // Reset the form after successful submission
        } else {
            console.log('Failed to create the activity. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        console.log('An error occurred while submitting the form. Please try again later.');
    }
}