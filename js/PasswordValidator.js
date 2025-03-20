class PasswordValidator {
    static LOWERCASE_CHARS = /[a-z]/;
    static UPPERCASE_CHARS = /[A-Z]/;
    static NUMBERS = /[0-9]/;
    static SPECIAL_CHARS = /[!@#$%^&*(),.?":{}|<>]/;

    constructor(password, lengthCharacter = 8) {
        this.password = password;
        this.lengthCharacter = lengthCharacter;
        this.limitLengthCharacter = lengthCharacter + 4;
        this.errors = [];
        this.validate = true;
        this.point = 0;
        this.differentCharacters = 0;
        this.assessment = '';
    }

    hasLengthCharacter() {
        if (this.password.length < this.lengthCharacter) {
            this.errors.push(`La contraseña debe tener una longitud de al menos ${this.lengthCharacter} caracteres.`);
            this.validate = false;
        } else {
            //this.differentCharacters++;
        }
    }

    hasLowercase() {
        if (!PasswordValidator.LOWERCASE_CHARS.test(this.password)) {
            this.errors.push('La contraseña debe tener al menos una letra minúscula.');
            this.validate = false;
        } else {
            this.differentCharacters++;
        }
    }

    hasUppercase() {
        if (!PasswordValidator.UPPERCASE_CHARS.test(this.password)) {
            this.errors.push('La contraseña debe tener al menos una letra mayúscula.');
            this.validate = false;
        } else {
            this.differentCharacters++;
        }
    }

    hasNumber() {
        if (!PasswordValidator.NUMBERS.test(this.password)) {
            this.errors.push('La contraseña debe tener al menos un número.');
            this.validate = false;
        } else {
            this.differentCharacters++;
        }
    }

    hasSpecialCharacter() {
        if (!PasswordValidator.SPECIAL_CHARS.test(this.password)) {
            this.errors.push('La contraseña debe tener al menos un símbolo.');
            this.validate = false;
        } else {
            this.differentCharacters++;
        }
    }

    evaluateComplexity() {
        if (parseInt(this.differentCharacters) == 0) {
            this.point = this.password.length > 0 ? 5 :0;
            this.assessment = 'Muy débil';
        }else if (parseInt(this.differentCharacters) == 1) {
            this.point = 15
            this.assessment = 'Muy débil';
        }else if(parseInt(this.differentCharacters) == 2){
            this.point = 25
            this.assessment = 'Débil';
        }else if(parseInt(this.differentCharacters) == 3){
            this.point = 60
            this.assessment = 'Moderadamente segura';
        }else if(parseInt(this.differentCharacters) == 4){
            this.point = 85
            this.assessment = 'Segura';
            if (this.password.length >= this.limitLengthCharacter) {
                this.point = this.point + 15;
                this.assessment = 'Muy segura';
            }
        }
    }

    isValid() {
        this.hasLengthCharacter();
        if (!this.validate) {
            this.evaluateComplexity();
            return { validate: this.validate, errors: this.errors, point: this.point, assessment : this.assessment };
        }

        this.hasLowercase();
        this.hasUppercase();
        this.hasNumber();
        this.hasSpecialCharacter();
        this.evaluateComplexity();

        return { validate: this.validate, errors: this.errors, point: this.point, assessment : this.assessment };
    }
}

// Ejemplo de uso
// const validator = new PasswordValidator("MiContraseña123");
// const result = validator.isValid();
// console.log(result);