using System;
namespace ClientBL.Validators
{
    public static class CredentialValidator
    {
        public static ValidationResponse ValidateCredentialValue(string value)
        {

            if (value == null || value.Length == 0)
            {
                return ValidationResponse.FieldCanotBeEmpty;
            }



            return ValidationResponse.OK;
 


            //return ValidationResponse.UnidentifiedError;

        }
    }
}
