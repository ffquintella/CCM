namespace Domain.Protocol
{
    public enum ObjectOperationStatus
    {
        Created, 
        Updated, 
        Deleted, 
        Error,
        Forbidden,
        NotFound
    }
}