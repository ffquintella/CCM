namespace Domain.Authentication
{
    public enum AuthenticationErrorType
    {
        NoError,
        RequestBadFormated,
        LoginDoesntExists, 
        BadPassword,
        UnkwonError
    }
}