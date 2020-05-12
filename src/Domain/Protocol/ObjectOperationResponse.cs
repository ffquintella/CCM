namespace Domain.Protocol
{
    public class ObjectOperationResponse
    {
        public ObjectOperationStatus Status { get; set; }
        
        public long IdRef { get; set; }
        
        public string Message { get; set; }
    }
}